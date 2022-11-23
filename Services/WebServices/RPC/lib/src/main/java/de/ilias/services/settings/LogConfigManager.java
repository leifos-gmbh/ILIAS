/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package de.ilias.services.settings;

import org.apache.logging.log4j.Level;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.apache.logging.log4j.core.LoggerContext;
import org.apache.logging.log4j.core.appender.RollingFileAppender;
import org.apache.logging.log4j.core.appender.rolling.DefaultRolloverStrategy;
import org.apache.logging.log4j.core.appender.rolling.SizeBasedTriggeringPolicy;
import org.apache.logging.log4j.core.config.Configuration;
import org.apache.logging.log4j.core.config.LoggerConfig;
import org.apache.logging.log4j.core.layout.PatternLayout;
import org.ini4j.Ini;

import java.io.File;
import java.io.FileReader;
import java.io.IOException;

/**
 *
 * @author stefan
 */
public class LogConfigParser {

	private final Logger logger = LogManager.getLogger(LogConfigParser.class);
	
	File file;
	Level level;
	
	
	public Level getLogLevel()
	{
		return this.level;
	}
	
	public File getLogFile()
	{
		return this.file;
	}

	public void setLogLevel(String logLevel) {

		this.Level = Level.toLevel(logLevel.trim(),Level.INFO);
	}

	public void setLogFile(String logFile) throws ConfigurationException, IOException {

		this.file = new File(logFile);
		if(!this.file.isAbsolute()) {
			logger.error("Absolute path to logfile required: {}", logFile);
			throw new ConfigurationException("Absolute path to logfile required: " + logFile);
		}
		if(this.file.isDirectory()) {
			logger.error("Absolute path to logfile required. Directory name given: {}", logFile);
			throw new ConfigurationException("Absolute path to logfile required: " + logFile);
		}
		if(this.file.createNewFile()) {
			logger.debug("Creating new log file {}", logFile);
		}
		else {
			logger.debug("Using existing log file: {}", this.file.getAbsolutePath());
		}
		if(!this.file.canWrite()) {
			throw new ConfigurationException("Cannot write to log file: {}",logFile);
		}
	}




	public void parse(String path) throws ConfigurationException {
		
		Ini prefs;
		try {

			prefs = new Ini(new FileReader(path));
			for(Ini.Section section : prefs.values()) {
				
				if(section.getName().equals("Server")) {
					if(section.containsKey("LogFile"))
						setLogFile(purgeString(section.get("LogFile")));
					if(section.containsKey("LogLevel"))
						setLogLevel(purgeString(section.get("LogLevel")));
				}
			}
		} catch (IOException e) {
			throw new ConfigurationException(e);
		}
	}

	private void initLogManager()
	{
		LoggerContext context = (LoggerContext) LogManager.getContext(false);
		Configuration config = context.getConfiguration();
		// keep ERROR from properties
		LoggerConfig rootConfig = config.getLoggerConfig(LogManager.ROOT_LOGGER_NAME);
		// set to ilServer.ini level
		LoggerConfig iliasConfig = config.getLoggerConfig("de.ilias");
		iliasConfig.setLevel(getLogLevel());
		// keep INFO level
		LoggerConfig iliasServerConfig = config.getLoggerConfig("de.ilias.ilServer");
		iliasConfig.setLevel(Level.INFO);

		// new rolling file appender
		PatternLayout fileLayout = PatternLayout.newBuilder()
				.withConfiguration(config)
				.withPattern("%d{ISO8601} %-5p %t (%F:%L) - %m%n")
				.build();

		DefaultRolloverStrategy strategy = DefaultRolloverStrategy.newBuilder()
				.withMax("7")
				.withMin("1")
				.withFileIndex("max")
				.withConfig(config)
				.build();

		RollingFileAppender file = RollingFileAppender.newBuilder()
				.setName("RollingFile")
				.withFileName(getLogFile().getAbsolutePath())
				.withFilePattern(getLogFile().getName() + "%d")
				.withStrategy(strategy)
				.withPolicy(SizeBasedTriggeringPolicy.createPolicy("100MB"))
				.setConfiguration(config)
				.setLayout(fileLayout)
				.build();
		file.start();
		config.addAppender(file);
		rootConfig.addAppender(file, getLogLevel(), null);
		iliasConfig.addAppender(file, getLogLevel(), null);
		iliasConfig.setAdditive(false);
		context.updateLoggers();
	}

	
	/**
	 * @param dirty
	 * @param replaceQuotes
	 * @return
	 */
	public String purgeString(String dirty,boolean replaceQuotes) {
		
		if(replaceQuotes) {
			return dirty.replace('"',' ').trim();
		}
		else {
			return dirty.trim();
		}
	}
	
	/**
	 * 
	 * @param dirty
	 * @return
	 */
	public String purgeString(String dirty) {
		
		return purgeString(dirty,false);
	}
	
}

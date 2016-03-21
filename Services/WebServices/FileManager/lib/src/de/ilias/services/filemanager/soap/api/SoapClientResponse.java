/*
 * To change this template, choose Tools | Templates and open the template in
 * the editor.
 */
package de.ilias.services.filemanager.soap.api;

import java.io.StringReader;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.xml.XMLConstants;
import javax.xml.bind.JAXBContext;
import javax.xml.bind.JAXBException;
import javax.xml.bind.Unmarshaller;
import javax.xml.parsers.ParserConfigurationException;
import javax.xml.parsers.SAXParserFactory;
import javax.xml.transform.sax.SAXSource;
import org.xml.sax.InputSource;
import org.xml.sax.SAXException;
import org.xml.sax.SAXNotRecognizedException;
import org.xml.sax.SAXNotSupportedException;
import org.xml.sax.XMLReader;

/**
 * Soap client repsonse
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
public class SoapClientResponse implements SoapClientInteraction {
	
	
	/**
	 * Unmarshall response
	 * @param sourceXml
	 * @param responseHandler
	 * @return
	 * @throws JAXBException 
	 */
	public Object unmarshallResponse(String sourceXml, Class responseHandler) throws JAXBException {
		
		try {
			JAXBContext context = JAXBContext.newInstance(responseHandler);
			
			SAXParserFactory spf = SAXParserFactory.newInstance();
			spf.setFeature(XMLConstants.FEATURE_SECURE_PROCESSING, true);
			spf.setFeature("http://apache.org/xml/features/nonvalidating/load-external-dtd", false);
			
			XMLReader xmlReader = spf.newSAXParser().getXMLReader();
			InputSource inputSource = new InputSource(new StringReader(sourceXml));
			SAXSource source = new SAXSource(xmlReader, inputSource);
			
			Unmarshaller unmarshaller = context.createUnmarshaller();
			return unmarshaller.unmarshal(source);
		} catch (ParserConfigurationException ex) {
			Logger.getLogger(SoapClientResponse.class.getName()).log(Level.SEVERE, null, ex);
		} catch (SAXNotRecognizedException ex) {
			Logger.getLogger(SoapClientResponse.class.getName()).log(Level.SEVERE, null, ex);
		} catch (SAXNotSupportedException ex) {
			Logger.getLogger(SoapClientResponse.class.getName()).log(Level.SEVERE, null, ex);
		} catch (SAXException ex) {
			Logger.getLogger(SoapClientResponse.class.getName()).log(Level.SEVERE, null, ex);
		}

		return null;
	}
	
}

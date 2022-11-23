/*
        +-----------------------------------------------------------------------------+
        | ILIAS open source                                                           |
        +-----------------------------------------------------------------------------+
        | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
        |                                                                             |
        | This program is free software; you can redistribute it and/or               |
        | modify it under the terms of the GNU General Public License                 |
        | as published by the Free Software Foundation; either version 2              |
        | of the License, or (at your option) any later version.                      |
        |                                                                             |
        | This program is distributed in the hope that it will be useful,             |
        | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
        | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
        | GNU General Public License for more details.                                |
        |                                                                             |
        | You should have received a copy of the GNU General Public License           |
        | along with this program; if not, write to the Free Software                 |
        | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
        +-----------------------------------------------------------------------------+
*/

package de.ilias.services.lucene.index.file;

import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.apache.tika.exception.TikaException;
import org.apache.tika.metadata.Metadata;
import org.apache.tika.parser.ParseContext;
import org.apache.tika.parser.pdf.PDFParser;
import org.apache.tika.sax.BodyContentHandler;
import org.xml.sax.SAXException;

import java.io.IOException;
import java.io.InputStream;

/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class TikaPDFHandler implements FileHandler {

	protected Logger logger = LogManager.getLogger(TikaPDFHandler.class);
	
	
	/**
	 * @throws IOException 
	 * @see de.ilias.services.lucene.index.file.FileHandler#getContent(java.io.InputStream)
	 */
	public String getContent(InputStream is) throws FileHandlerException {

		BodyContentHandler handler = new BodyContentHandler();
		Metadata md = new Metadata();
		PDFParser parser = new PDFParser();
		ParseContext context = new ParseContext();

		try {
			parser.parse(is, handler, md, context);
			logger.debug("Parsed pdf content: {}", handler.toString());
			return handler.toString();
		} catch (IOException e) {
			logger.warn(e.getMessage());
			throw new FileHandlerException(e);
		} catch (SAXException e) {
			logger.warn(e.getMessage());
			throw new FileHandlerException(e);
		} catch (TikaException e) {
			logger.warn(e.getMessage());
			throw new FileHandlerException(e);
		}
	}

	/**
	 * @see de.ilias.services.lucene.index.file.FileHandler#transformStream(java.io.InputStream)
	 */
	public InputStream transformStream(InputStream is) {
		// TODO Auto-generated method stub
		return null;
	}

}

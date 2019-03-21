import PageContentInterface from './PageContentInterface';
import PCParagraph from './PCParagraph';

/**
 * Operation factory
 */
export default class PageContentFactory {

    constructor() {
    }

    /**
     * Get page content object
     * @param {string} type
     * @returns {PageContentInterface}
     */
    pageContent(type: string): PageContentInterface {
        switch (type)
        {
            case "par":
                return new PCParagraph();
                break;
        }
    }
}
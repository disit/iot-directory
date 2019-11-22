/*
 * Describes an XML selector
 */
module.exports = class XMLSelector {
    /*
     * Constructor with the xml path and the indexes to extract
     */
    constructor(p, i) {
        this.path = p;
        this.indexes = i;
    }
};

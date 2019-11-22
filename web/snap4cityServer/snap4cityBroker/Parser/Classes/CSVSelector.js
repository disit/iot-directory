/*
 * Describes a CSV selector
 */
module.exports = class CSVSelector {
    /*
     * Constructor with array of separators and the indexes of the element/elements that will be extracted
     */
    constructor(s, i) {
        this.separators = s;
        this.indexes = i;
    }
};

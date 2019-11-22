/*
 * Rule class
 */
module.exports = class Rule {
    /*
     * s: selector
     * f: format
     */
    constructor(s, f) {
        this.selector = s;
        this.format = f;
    }
};
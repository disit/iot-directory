/*
 * Describes a string manipulation selector (SM rule).
 * A SM selector helps to manipulate string (e.g. substring, replace functions..)
 */
module.exports = class SMSelector {
    /*
     * Constructor with the function to apply and its parameters
     */
    constructor(f, p) {
        this.function = f;
        this.param = p;
    }
};

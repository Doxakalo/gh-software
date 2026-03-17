class NumberUtils {

    constructor() {

    }



    isInt(n) {
        return Number(n) === n && n % 1 === 0;
    };

    isFloat(n) {
        return Number(n) === n && n % 1 !== 0;
    };

    roundNumber(number) {
        if (this.isFloat(number)) {
            return parseFloat(number.toFixed(1));
        }
        return parseFloat(number);
    };
}

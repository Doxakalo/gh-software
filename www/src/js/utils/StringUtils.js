class StringUtils {

    constructor() {

    }

    replaceInStringDFC(string) {
        return string.split('.').join(',');
    }

    replaceInStringCFD(string) {
        return string.split(',').join('.');
    }
}

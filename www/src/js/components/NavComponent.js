class NavComponent {
    constructor() {
        this._header = $("header");
        this._nav = $("nav", this._header);
        this._search = $("#search", this._header);
        this._searchBar = $("#searchBar", this._search);

        this.init();
    }

    init() {
        $("#searchIcon", this._search).on('click', (e) => {
            var _this = $(e.currentTarget);
            setTimeout(() => {
                this._searchBar.val("");
                this._searchBar.focus();
                this._header.toggleClass("activeSearch");
            }, 100);
        });

        $("#closeIcon", this._search).on('click', (e) => {
            var _this = $(e.currentTarget);
            setTimeout(() => {
                this._searchBar.val("");
                this._searchBar.focus();
                this._header.toggleClass("activeSearch");
            }, 100);
        });
    }
}
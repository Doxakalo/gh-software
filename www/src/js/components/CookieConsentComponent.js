class CookieConsentComponent {
    constructor() {
        let _this = this;
        this.cookieC = initCookieConsent();
        this.expiration = 180; //in days;
        this.cookieC.run({
            current_lang: locale,
            autoclear_cookies: true,                   // default: false
            page_scripts: true,                        // default: false
            level: [
                "necessary",
                "analytics",
                "marketing"
            ],
            onFirstAction: function (cookie) {
                cookie.action = "first_action";
                cookie.expiration = _this.expiration;

                $.ajax({
                    url: cookieUpdateSessionExpiration,
                    method: "POST",
                    data: cookie
                });


            },
            onAccept: function (cookie) {
                gtag('consent', 'update', {
                    ad_storage: (cookie.level.includes("analytics") || cookie.level.includes("marketing") ? "granted" : "denied"),
                    analytics_storage: (cookie.level.includes("analytics") ? "granted" : "denied"),
                    personalization_storage: (cookie.level.includes("marketing") ? "granted" : "denied"),
                });
            },
            onChange: function (cookie, changed_categories) {
                if(!cookie.level.includes("marketing")){
                    _this.delete_cookie("affiliateid");
                    _this.delete_cookie("affiliate");
                }


                cookie.action = "update";
                cookie.expiration = _this.expiration;

                $.ajax({
                    url: cookieUpdateSessionExpiration,
                    method: "POST",
                    data: cookie
                });


                console.log("y");

                if(!cookie.level.includes("analytics")){
                    console.log("x");
                    var gaCookies = ["_ga", "_gcl_au", "_gid"];

                    for (const [key, value] of Object.entries(_this.getCookies())) {
                        gaCookies.forEach(function(currentValue){
                            if(key.includes(currentValue)){
                                console.log("todelete");
                                console.log(key);
                                _this.delete_cookie(key);
                            }
                        });
                    }
                }


                gtag('consent', 'update', {
                    ad_storage: (cookie.level.includes("analytics") ? "granted" : "denied"),
                    analytics_storage: (cookie.level.includes("analytics") ? "granted" : "denied"),
                    personalization_storage: (cookie.level.includes("marketing") ? "granted" : "denied"),
                });
            },
            gui_options: {
                consent_modal: {
                    layout: 'bar',               // box/cloud/bar
                    position: 'bottom center',     // bottom/middle/top + left/right/center
                    transition: 'slide',           // zoom/slide
                    swap_buttons: false            // enable to invert buttons
                },
                settings_modal: {
                    layout: 'box',                 // box/bar
                    position: 'right',           // left/right
                    transition: 'slide'            // zoom/slide
                }
            },
            languages: _this.defineTranslate()
        });

    }

    delete_cookie(name) {
        document.cookie = name+'=; Max-Age=0; path=/; domain=' + location.host;
    }

    getCookies(){
        var pairs = document.cookie.split(";");
        var cookies = {};
        for (var i=0; i<pairs.length; i++){
            var pair = pairs[i].split("=");
            cookies[(pair[0]+'').trim()] = unescape(pair.slice(1).join('='));
        }
        return cookies;
    }
    defineTranslate(){
        let translates  = {
            cs: {
                consent_modal: {
                    title: 'Používáme cookies',
                    description: 'Používáme <a href="'+privacyPolicyLink+'">cookies</a> za účelem správného fungování webu a k marketingovým účelům. Pokud souhlasíte, klepněte na "přijmout". Nebo se podívejte na podrobné nastavení a přizpůsobte si  <a href="javascript:void(0);" data-cc="c-settings">cookies</a> svým potřebám.<br><br> <button type="button"  class="button" data-cc="accept-all">Přijmout</button>  <button type="button"   class="button" data-cc="accept-necessary">Odmítnout volitelné</button> <button   class="button" type="button" data-cc="c-settings">Podrobné nastavení...</button>',
                },
                settings_modal: {
                    title: 'Nastavení cookies',
                    save_settings_btn: 'Uložit volby',
                    accept_all_btn: 'Přijmout vše',
                    reject_all_btn: 'Odmítnout všechny volitelné',
                    close_btn_label: 'Zavřít',
                    cookie_table_headers: [
                        {col1 : 'Cookie'},
                        {col2 : 'Doména'},
                        {col3 : 'Expirace'},
                        {col4 : 'Popis'},
                        {col5 : 'Zpracovatel'}
                    ],
                    blocks: [
                        {
                            title: 'Používáme Cookies',
                            description: 'Soubory cookie používáme k analýze údajů o našich návštěvnících, ke zlepšení našich webových stránek, zobrazení personalizovaného obsahu a k tomu, abychom vám poskytli skvělý zážitek z webu.'
                        }, {
                            title: 'Funkční cookies',
                            description: 'Tato stránka obsahuje soubory cookie, které jsou nezbytné pro fungování našich webových stránek. Podílejí se na funkcích, jako jsou jazykové preference, distribuce provozu nebo udržování uživatelské relace. Nelze je zakázat.',
                            toggle: {
                                value: 'necessary',
                                enabled: true,
                                readonly: true          // cookie categories with readonly=true are all treated as "necessary cookies"
                            },
                            cookie_table: [             // list of all expected cookies
                                {
                                    col1: 'cc_cookie',       // match all cookies starting with "_ga"
                                    col2: '24usoftware.com',
                                    col3: '182 dní',
                                    col4: 'Ukládá souhlas/nesouhlas uživatele s používáním cookies.',
                                    col5: '24U s.r.o.',
                                },
                                {
                                    col1: 'PHPSESSID',
                                    col2: '24usoftware.com',
                                    col3: 'Relace',
                                    col4: 'Ukládá informace o uživatelově relaci.',
                                    col5: '24U s.r.o.',
                                },
                                {
                                    col1: '_nss',
                                    col2: '24usoftware.com',
                                    col3: 'Relace',
                                    col4: 'Součást programovacího frameworku, který zajišťuje vyšší bezpečnost webové aplikace.',
                                    col5: '24U s.r.o.',
                                },
                                {
                                    col1: 'first_visit_time_cookie',
                                    col2: '24usoftware.com',
                                    col3: '1 rok',
                                    col4: 'Ukládá čas, kdy uživatel navštívil webovou stránku (slouží jako část antispamové ochrany pro odesílání formulářů). Ukládá se pouze čas příchodu na stránku.',
                                    col5: '24U s.r.o.',
                                }
                            ]
                        }, {
                            title: 'Analytická cookies',
                            description: 'Používáme nástroje, které nám umožňují analyzovat používání naší stránky. Tyto údaje nám umožňují zlepšit uživatelské rozhraní naší webové služby.',
                            toggle: {
                                value: 'analytics',     // your cookie category
                                enabled: true,
                                readonly: false
                            },
                            cookie_table: [             // list of all expected cookies
                                {
                                    col1: '^_ga',       // match all cookies starting with "_ga"
                                    col2: '24usoftware.com',
                                    col3: '2 year',
                                    col4: "Cookie je součástí Google Analytics, které využíváme k anonymnímu měření statistik a představě o chování uživatelů. Data získaná z Google Analytics nám pomáhají webové stránky efektivně vylepšovat.",
                                    col5: 'Alphabet Inc.',
                                    is_regex: true
                                },
                                {
                                    col1: '_ga',       // match all cookies starting with "_ga"
                                    col2: '24usoftware.com',
                                    col3: '2 year',
                                    col4: "Cookie je součástí Google Analytics, které využíváme k anonymnímu měření statistik a představě o chování uživatelů. Data získaná z Google Analytics nám pomáhají webové stránky efektivně vylepšovat.",
                                    col5: 'Alphabet Inc.',
                                },
                                {
                                    col1: '_gid',
                                    col2: '24usoftware.com',
                                    col3: '1 day',
                                    col4: "Cookie je součástí Google Analytics, které využíváme k anonymnímu měření statistik a představě o chování uživatelů. Data získaná z Google Analytics nám pomáhají webové stránky efektivně vylepšovat.",
                                    col5: 'Alphabet Inc.',
                                },
                                {
                                    col1: '^_gat_gtag_UA',
                                    col2: '24usoftware.com',
                                    col3: '1 minute',
                                    col4: "Cookie je součástí Google Analytics, které využíváme k anonymnímu měření statistik a představě o chování uživatelů. Data získaná z Google Analytics nám pomáhají webové stránky efektivně vylepšovat.",
                                    col5: 'Alphabet Inc.',
                                    is_regex: true
                                }
                            ]
                        }, {
                            title: 'Marketing cookies',
                            description: 'Tato skupina souborů cookis se používá k zobrazování reklam na témata, která Vás zajímají. Umožňuje také sledovat naše marketingové aktivity a pomáhá měřit výkon reklam.',
                            toggle: {
                                value: 'marketing',
                                enabled: false,
                                readonly: false
                            },
                            cookie_table: [             // list of all expected cookies
                                {
                                    col1: 'affiliate',       // match all cookies starting with "_ga"
                                    col2: '24usoftware.com',
                                    col3: '24 hodin',
                                    col4: 'Cookie je součástí našeho Affiliate trackingu. Slouží pro vyhodnocení nároků na provize našim partnerům.',
                                    col5: '24U s.r.o.',
                                },
                                {
                                    col1: 'affiliateid',       // match all cookies starting with "_ga"
                                    col2: '24usoftware.com',
                                    col3: '24 hodin',
                                    col4: 'Cookie je součástí našeho Affiliate trackingu. Slouží pro vyhodnocení nároků na provize našim partnerům.',
                                    col5: '24U s.r.o.',
                                },
                            ]
                        }
                    ]
                }
            },
            en: {
                consent_modal: {
                    title: 'We use cookies',
                    description: 'We use <a href="'+privacyPolicyLink+'">cookies</a> to ensure that we give you best browsing experience on our website, and also for certain marketing purposes. Click on “accept” if you wish to allow all <a href="'+privacyPolicyLink+'">cookies</a>. Or customize your settings in the <a  href="javascript:void(0);" data-cc="c-settings">detailed settings</a>. <br><br> <button type="button"  class="button" data-cc="accept-all">Accept</button>  <button type="button"   class="button" data-cc="accept-necessary">Reject optional</button> <button   class="button" type="button" data-cc="c-settings">Detailed settings...</button>'
                },
                settings_modal: {
                    title: 'Cookies settings',
                    save_settings_btn: 'Save settings',
                    accept_all_btn: 'Accept all',
                    reject_all_btn: 'Reject all optional',
                    close_btn_label: 'Close',
                    cookie_table_headers: [
                        {col1 : 'Cookie'},
                        {col2 : 'Domain'},
                        {col3 : 'Expiration'},
                        {col4 : 'Description'},
                        {col5 : 'Owner'}
                    ],
                    blocks: [
                        {
                            title: 'We use Cookies',
                            description: 'We use cookies to analyse data about our visitors, to improve our website, to display personalised content and to give you a great web experience.'
                        }, {
                            title: 'Functional cookies',
                            description: 'This group contains cookies that are necessary for our websites to work. They take part in functionalities like language preferences, traffic distribution or keeping user session. They cannot be disabled.',
                            toggle: {
                                value: 'necessary',
                                enabled: true,
                                readonly: true          // cookie categories with readonly=true are all treated as "necessary cookies"
                            },
                            cookie_table: [             // list of all expected cookies
                                {
                                    col1: 'first_visit_time_cookie',       // match all cookies starting with "_ga"
                                    col2: '24usoftware.com',
                                    col3: '1 year',
                                    col4: "It saves the time when a user visited a web page (serves as part of anti-spam protection for form submissions). Only the time of arrival to the page is saved.",
                                    col5: '24U s.r.o.',
                                },
                                {
                                    col1: 'cc_cookie',       // match all cookies starting with "_ga"
                                    col2: '24usoftware.com',
                                    col3: '182 days',
                                    col4: "It saves the user's approval/disapproval of the use of cookies.",
                                    col5: '24U s.r.o.',
                                },
                                {
                                    col1: 'PHPSESSID',
                                    col2: '24usoftware.com',
                                    col3: 'Session',
                                    col4: "It saves information about the user's session.",
                                    col5: '24U s.r.o.',
                                },
                                {
                                    col1: '_nss',
                                    col2: '24usoftware.com',
                                    col3: 'Session',
                                    col4: 'The part of a programming framework that makes web application more secure.',
                                    col5: '24U s.r.o.',
                                }
                            ]
                        }, {
                            title: 'Analytical cookies',
                            description: 'We use tools that let us analyze the usage of our page. Such data lets us improve the user experience of our web service.',
                            toggle: {
                                value: 'analytics',     // your cookie category
                                enabled: true,
                                readonly: false
                            },
                            cookie_table: [             // list of all expected cookies
                                {
                                    col1: '^_ga',       // match all cookies starting with "_ga"
                                    col2: '24usoftware.com',
                                    col3: '2 year',
                                    col4: "The cookie is part of Google Analytics, which we use to anonymously measure statistics and to understand user behaviour. The data obtained from Google Analytics helps us to improve the website effectively.",
                                    col5: 'Alphabet Inc.',
                                    is_regex: true
                                },
                                {
                                    col1: '_ga',       // match all cookies starting with "_ga"
                                    col2: '24usoftware.com',
                                    col3: '2 year',
                                    col4: "The cookie is part of Google Analytics, which we use to anonymously measure statistics and to understand user behaviour. The data obtained from Google Analytics helps us to improve the website effectively.",
                                    col5: 'Alphabet Inc.',
                                },
                                {
                                    col1: '_gid',
                                    col2: '24usoftware.com',
                                    col3: '1 day',
                                    col4: "The cookie is part of Google Analytics, which we use to anonymously measure statistics and to understand user behaviour. The data obtained from Google Analytics helps us to improve the website effectively.",
                                    col5: 'Alphabet Inc.',
                                },
                                {
                                    col1: '^_gat_gtag_UA',
                                    col2: '24usoftware.com',
                                    col3: '1 minute',
                                    col4: "The cookie is part of Google Analytics, which we use to anonymously measure statistics and to understand user behaviour. The data obtained from Google Analytics helps us to improve the website effectively.",
                                    col5: 'Alphabet Inc.',
                                    is_regex: true
                                }
                            ]
                        }, {
                            title: 'Marketing cookies',
                            description: "This group of cookies is used to show you ads of topics that you are interested in. It also lets us monitor our marketing activities, it helps to measure the performance of our ads.",
                            toggle: {
                                value: 'marketing',
                                enabled: false,
                                readonly: false
                            },
                            cookie_table: [             // list of all expected cookies
                                {
                                    col1: 'affiliate',       // match all cookies starting with "_ga"
                                    col2: '24usoftware.com',
                                    col3: '24 hours',
                                    col4: "Cookie is part of our affiliate tracking. It is used to evaluate commission claims to our partners.",
                                    col5: '24U s.r.o.',
                                },
                                {
                                    col1: 'affiliateid',       // match all cookies starting with "_ga"
                                    col2: '24usoftware.com',
                                    col3: '24 hours',
                                    col4: "Cookie is part of our affiliate tracking. It is used to evaluate commission claims to our partners.",
                                    col5: '24U s.r.o.',
                                }
                            ]
                        }
                    ]
                }
            }
        }
        return translates;
    }
}

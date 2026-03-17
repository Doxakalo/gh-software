$(document).ready(function () {
    let page;
    let page2;
    let templateId = $('body').data('template');

    /*
     * Init Nette JS
     */
    $.nette.init();


    /*
     * Init page controller by template
     */
    switch (templateId) {
        case 'homepage':
            page = new HomepageController();
            break;

        case 'filemaker-consulting':
            page = new FileMakerConsultingController();
            break;

        case 'filemaker-licensing':
            page = new FileMakerLicensingController();
            break;

        case 'quality-assurance':
            page = new QualityAssuranceController();
            break;

        case 'hardware-integration':
            page = new HardwareIntegrationController();
            break;

        case 'stories':
            page = new StoriesController();
            break;

        case 'support':
            page = new SupportController();
            break;

        case 'sla':
            page = new SlaController();
            break;

        case 'news':
            page = new NewsController();
            break;

        case 'news-detail':
            page = new NewsDetailController();
            break;

        case 'tools':
            page = new ToolsController();
            break;

        case 'tools-detail':
            page = new ToolsDetailController();
            break;

        case 'termsofuse':
            page = new TermsOfUseController();
            break;

        case 'company':
            page = new CompanyController();
            break;

        case 'press':
            page = new PressController();
            break;

        case 'privacypolicy':
            page = new PrivacyPolicyController();
            break;

        case 'productpages':
            page = new ProductPagesController();
            break;

        case 'stories-detail':
            page = new StoriesDetailController();
            break;

        case 'search':
            page = new SearchController();
            break;

        default:
            // init generic page controller for other templates
            page = new PageController();
            break;
    }


    /*
     * Global elements
     */
    let footerSubscribeForm = new FooterSubscribeForm('#footer-subscribe-form');
    let footerContactForm = new FooterContactForm('#footer-contact-form');


    /*
     * Init share links
     */
    SharingUtils.initShareLinks();


    /*
     * Legacy global elements
     * TODO cleanup, remove unused, move to page controllers
     */
    let nav = new NavComponent();
    let form = new FormComponent();
    let CC = new CookieConsentComponent();



});



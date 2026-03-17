<?php

namespace App;

use Nette;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;


final class RouterFactory
{
    use Nette\StaticClass;
    
    public static function createRouter(): Nette\Application\IRouter
    {
        $router = new RouteList;

        // Custom Apps
        $router[] = new Route('[<locale=en en>/]custom-apps', 'CustomApps:default');
        $router[] = new Route('[<locale=cs cs>/]aplikace-na-miru', 'CustomApps:default');

        // FileMaker Consulting
        $router[] = new Route('[<locale=en en>/]filemaker-consulting', 'FileMakerConsulting:default');
        $router[] = new Route('[<locale=cs cs>/]konzultace-filemaker', 'FileMakerConsulting:default');
        
        // FileMaker Licensing
        $router[] = new Route('[<locale=en en>/]filemaker', 'FileMakerLicensing:default');
        $router[] = new Route('[<locale=cs cs>/]cs/filemaker', 'FileMakerLicensing:default');
        $router[] = new Route('[<locale=en en>/]filemaker-licensing', 'FileMakerLicensing:default'); // OLD URL
        $router[] = new Route('[<locale=cs cs>/]licence-filemaker', 'FileMakerLicensing:default');

        // QA
        $router[] = new Route('[<locale=en en>/]quality-assurance', 'QualityAssurance:default');
        $router[] = new Route('[<locale=cs cs>/]kontrola-kvality', 'QualityAssurance:default');

        // Hardware Integration
        $router[] = new Route('[<locale=en en>/]hardware-integration', 'HardwareIntegration:default');
        $router[] = new Route('[<locale=cs cs>/]integrace-hardware', 'HardwareIntegration:default');

        // SLA
        $router[] = new Route('[<locale=en en>/]sla', 'SLA:default');
        $router[] = new Route('[<locale=cs cs>/]servisni-smlouvy', 'SLA:default');

        // Xgode Terms
        $router[] = new Route('[<locale=en cs|en>/]xgode-terms', 'CustomStaticPage:xgodeTerms');

        // GDPR
        $router[] = new Route('[<locale=en en>/]gdpr', 'CustomStaticPage:gdpr');
        $router[] = new Route('[<locale=cs cs>/]oou', 'CustomStaticPage:oou');
        $router[] = new Route('[<locale=en cs>/]gdpr', 'CustomStaticPage:oou');
        $router[] = new Route('[<locale=cs en>/]oou', 'CustomStaticPage:gdpr');

        // Support
        $router[] = new Route('[<locale=en en>/]support', 'Support:default');
        $router[] = new Route('[<locale=cs cs>/]podpora', 'Support:default');

        // Privacy Policy
        $router[] = new Route('[<locale=en en>/]privacy-policy', 'PrivacyPolicy:default');
        $router[] = new Route('[<locale=cs cs>/]ochrana-soukromi', 'PrivacyPolicy:default');
        // Terms of Use
        $router[] = new Route('[<locale=en en>/]terms-of-use', 'TermsOfUse:default');
        $router[] = new Route('[<locale=cs cs>/]podminky-uziti', 'TermsOfUse:default');

        // Company
        $router[] = new Route('[<locale=en en>/]company', 'Company:default');
        $router[] = new Route('[<locale=cs cs>/]o-nas', 'Company:default');

        // Examples
        $router[] = new Route('[<locale=en cs|en>/]examples', 'Examples:default');

        // Stories
        $router[] = new Route('[<locale=en en>/]our-skills-and-stories-of-our-customers', 'Stories:list');
        $router[] = new Route('[<locale=cs cs>/]ukazky-reseni-a-pribehy-nasich-klientu', 'Stories:list');

        $router[] = new Route('[<locale=en en>/]our-skill/<url>', 'Stories:skillDetail');
        $router[] = new Route('[<locale=cs cs>/]ukazka-reseni/<url>', 'Stories:skillDetail');

        $router[] = new Route('[<locale=en en>/]stories/synetrans', 'Stories:synetrans');
        $router[] = new Route('[<locale=cs cs>/]pribehy/synetrans', 'Stories:synetrans');

        $router[] = new Route('[<locale=en en>/]stories/breedbook', 'Stories:breedbook');
        $router[] = new Route('[<locale=cs cs>/]pribehy/breedbook', 'Stories:breedbook');

        $router[] = new Route('[<locale=en en>/]stories/jogs', 'Stories:jogs');
        $router[] = new Route('[<locale=cs cs>/]pribehy/jogs', 'Stories:jogs');

        $router[] = new Route('[<locale=en en>/]stories/adiutor', 'Stories:adiutor'); //Default
        $router[] = new Route('[<locale=cs cs>/]pribehy/adiutor', 'Stories:adiutor'); //Default
        $router[] = new Route('[<locale=en cs|en>/]stories/auditor', 'Stories:adiutor'); // OLD ( REQUIRE TO STAY )

        $router[] = new Route('[<locale=en en>/]stories/jennpro', 'Stories:jennpro');
        $router[] = new Route('[<locale=cs cs>/]pribehy/jennpro', 'Stories:jennpro');
        
        $router[] = new Route('[<locale=en cs|en>/]stories/ewemanage', 'Stories:ewemanage');
        
        // News
        $router[] = new Route('[<locale=en cs|en>/]news', 'News:list');
        $router[] = new Route('[<locale=en cs|en>/]news[/<url>]', 'News:detail');
        $router[] = new Route('[<locale=en cs|en>/]news/share-news/<type>', 'News:shareNews');
        $router[] = new Route('[<locale=en cs|en>/]news/image/<id>', 'News:image');
        $router[] = new Route('[<locale=en cs|en>/]news/image-social-news/<id>/<newsId>', 'News:imageOG');

        // Press
        $router[] = new Route('[<locale=en cs|en>/]press', 'Press:list');
        $router[] = new Route('[<locale=en cs|en>/]press/<id>', 'Press:detail');

        // Tools
        $router[] = new Route('[<locale=en cs|en>/]tools', 'Tools:list');

        //$router[] = new Route('[<locale=en cs|en>/]swiper', 'Tools:swiperTest'); //swiper test page - remove after development
        $router[] = new Route('[<locale=en cs|en>/]simplehasp', 'Tools:simpleHASP');
        $router[] = new Route('[<locale=en cs|en>/]simplesound', 'Tools:simpleSound');
        $router[] = new Route('[<locale=en cs|en>/]toolbox', 'Tools:toolbox');
        $router[] = new Route('[<locale=en cs|en>/]simpletalk', 'Tools:simpleTalk');
        $router[] = new Route('[<locale=en cs|en>/]phidgets', 'Tools:bridgeForPhidgets');
        $router[] = new Route('[<locale=en cs|en>/]arduino', 'Tools:bridgeForArduino');
        $router[] = new Route('[<locale=en cs|en>/]phonecompanion', 'Tools:phoneCompanion');
        $router[] = new Route('[<locale=en cs|en>/]simplefile', 'Tools:simpleFile');
        $router[] = new Route('[<locale=en cs|en>/]simpledialog', 'Tools:simpleDialog');
        $router[] = new Route('[<locale=en cs|en>/]fmbench', 'Tools:bench');
        $router[] = new Route('[<locale=en cs|en>/]fmrestor', 'Tools:fmRESTor');
        $router[] = new Route('[<locale=en cs|en>/]benchtest', 'Tools:benchTest');
        $router[] = new Route('[<locale=en cs|en>/]benchtest/cache/<token>', 'Tools:benchTestCache');
        $router[] = new Route('[<locale=en cs|en>/]benchtest/results', 'Tools:benchTestResults');
        $router[] = new Route('[<locale=en cs|en>/]logmonitor', 'Tools:logMonitor');
        $router[] = new Route('[<locale=en cs|en>/]xgode', 'Tools:xgode');
        $router[] = new Route('[<locale=en cs|en>/]gonector', 'Tools:gonector');
        $router[] = new Route('[<locale=en cs|en>/]tracksy', 'Tools:tracksy');


        $router[] = new Route('[<locale=en cs|en>/]cancel-subscription', 'CancelSubscription:default');

        // Custom Product Pages
        $router[] = new Route('[<locale=en cs|en>/]breedbook', 'ProductPages:breedbook');
        $router[] = new Route('[<locale=en cs|en>/]spedisimo', 'ProductPages:spedisimo');
        $router[] = new Route('[<locale=en cs|en>/]ledcontroller', 'ProductPages:led');
        $router[] = new Route('[<locale=en cs|en>/]scavent', 'ProductPages:scavent');

        $router[] = new Route('[<locale=en cs|en>/]search', 'Search:default');

        // STORE
        $router[] = new Route('[<locale=en cs|en>/]cart/create', 'Cart:create');
        $router[] = new Route('[<locale=en cs|en>/]cart/order/<orderId>', 'Cart:order');
        $router[] = new Route('[<locale=en cs|en>/]cart/get/<orderId>', 'Cart:get');
        $router[] = new Route('[<locale=en cs|en>/]cart/cancel/<orderId>', 'Cart:cancel');

        // Tech
        $router[] = new Route('[<locale=en cs|en>/]generate-searchable-records', 'Tech:GenerateSearchRecords');
        $router[] = new Route('[<locale=en cs|en>/]mark-contact-log-as-spam/<id>', 'Tech:MarkContactLogAsSpam');

        // Cookie
        $router[] = new Route('[<locale=en cs|en>/]cookie', 'Cookie:cookie');
        $router[] = new Route('[<locale=en cs|en>/]set-shortlinks-administration', 'Cookie:shortlinkAdminCookie');

        // HOMEPAGE
        $router[] = new Route('[<locale=en cs|en>/]', 'Homepage:default');

        // GENERIC
        $router[] = new Route('[<locale=cs cs|en>/]<presenter>/<action>', 'Homepage:default');
        return $router;
    }
}

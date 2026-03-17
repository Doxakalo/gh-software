<?php

namespace App\Forms;

use Nette\Application\UI\Form;
use Monolog\Logger;

use App\Service\Config\ApplicationConfig;
use Contributte\Translation\Translator;
use Contributte;

class ShortlinkForm
{
    CONST TRANSLATE_PATH = "forms.shortlinks.";

    private $translator;
    private $logger;

    /** @persistent */
    public $locale;
    public $applicationConfig;

    public function __construct(Translator $translator,  Logger $logger, ApplicationConfig $applicationConfig)
    {
        $this->translator = $translator;
        $this->applicationConfig = $applicationConfig;
        $this->logger = $logger;

    }

    public function create()
    {

        $form = new Form;

        $form->addText("url", $this->translator->translate(self::TRANSLATE_PATH . "items.url.label"))
            ->setHtmlAttribute("placeholder", $this->translator->translate(self::TRANSLATE_PATH . "items.url.placeholder"))
            ->setRequired($this->translator->translate(self::TRANSLATE_PATH . "items.url.validation.required"));

        $form->addSelect("category", $this->translator->translate(self::TRANSLATE_PATH . "items.category.label"))
            ->setHtmlAttribute("placeholder", $this->translator->translate(self::TRANSLATE_PATH . "items.category.placeholder"))
            ->setItems($this->applicationConfig->getConfig()['shortlinks']['categories'], false)
            ->setRequired($this->translator->translate(self::TRANSLATE_PATH . "items.category.validation.required"));

        $form->addText("shortlink", $this->translator->translate(self::TRANSLATE_PATH . "items.shortlink.label"))
            ->setHtmlAttribute("placeholder", $this->translator->translate(self::TRANSLATE_PATH . "items.shortlink.placeholder"));

        $form->addText("note", $this->translator->translate(self::TRANSLATE_PATH . "items.note.label"))
            ->setHtmlAttribute("placeholder", $this->translator->translate(self::TRANSLATE_PATH . "items.note.placeholder"))
            ->setRequired($this->translator->translate(self::TRANSLATE_PATH . "items.note.validation.required"));

        $form->addInteger("min_click", $this->translator->translate(self::TRANSLATE_PATH . "items.min_click.label"))
            ->setHtmlAttribute("placeholder", $this->translator->translate(self::TRANSLATE_PATH . "items.min_click.placeholder"))
            ->setHtmlAttribute("min", 0)
            ->setRequired($this->translator->translate(self::TRANSLATE_PATH . "items.min_click.validation.required"))
            ->addRule(Form::Min, $this->translator->translate(self::TRANSLATE_PATH . "items.min_click.validation.minimum"), 0);

        $form->addInteger("max_click", $this->translator->translate(self::TRANSLATE_PATH . "items.max_click.label"))
            ->setHtmlAttribute("placeholder", $this->translator->translate(self::TRANSLATE_PATH . "items.max_click.placeholder"))
            ->setHtmlAttribute("min", 0)
            ->setRequired($this->translator->translate(self::TRANSLATE_PATH . "items.max_click.validation.required"))
            ->addRule(Form::Min,  $this->translator->translate(self::TRANSLATE_PATH . "items.max_click.validation.minimum"), 0);
        ;

        $form->addInteger("days_limit", $this->translator->translate(self::TRANSLATE_PATH . "items.days_limit.label"))
            ->setHtmlAttribute("placeholder", $this->translator->translate(self::TRANSLATE_PATH . "items.days_limit.placeholder"))
            ->setHtmlAttribute("min", 0)
            ->setRequired($this->translator->translate(self::TRANSLATE_PATH . "items.days_limit.validation.required"))
            ->addRule(Form::Min,  $this->translator->translate(self::TRANSLATE_PATH . "items.days_limit.validation.minimum"), 0);

        $form->addHidden("flag_monitored")
            ->setRequired();
        $form->addHidden("expected_string");
        //    ->setRequired(); TODO


        $form->addSubmit("submit", $this->translator->translate(self::TRANSLATE_PATH . "items.submit.label"))
            ->setHtmlAttribute("class", "button");


        $form->onSuccess[] = array($this, 'process');

        return $form;
    }

    public function process(Form $form, $values)
    {
        try {
            $parameters = [
                'token' => sha1(date("mdY").'create_force'),
                'action' => 'create_force',
                'len' => 12,
                'url' => $values->url,
                'shortcode' => $values->shortlink,
                'note' => $values->note,
                'group' => $values->category,
                'access_limits_min_clicks' => $values->min_click,
                'access_limits_max_clicks' => $values->max_click,
                'access_limits_days' => $values->days_limit,
                'expected_string' => $values->expected_string,
                'flag_monitored' => $values->flag_monitored,
            ];
            $query = http_build_query($parameters);
            $shortlink_api_url = $this->applicationConfig->getConfig()["shortlinks-url"].'?'.$query;
            $shortlink_result = file_get_contents($shortlink_api_url);
            if(substr($shortlink_result, 0, 5) == "ERROR"){
                if($shortlink_result === 'ERROR: SHORTCODE EXISTS') $form->addError($this->translator->translate(self::TRANSLATE_PATH . "errors.shortcode-exists"));
                else $form->addError($this->translator->translate(self::TRANSLATE_PATH . "errors.unexpected"));
            }
        } catch (\Exception $e) {
            $this->logger->error("Unsuccesfull create shortlink from news - " . $values->shortlink . " with url " . $values->url . "; Result: " . json_encode($shortlink_result));
            $form->addError($this->translator->translate(self::TRANSLATE_PATH . "errors.unexpected"));
        }

        return $shortlink_result;
    }
}

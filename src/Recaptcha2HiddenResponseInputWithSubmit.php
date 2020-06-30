<?php

namespace bvb\recaptcha;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\InputWidget;

/**
 * Uses Recaptcha2's functionality for binding a submit button: https://developers.google.com/recaptcha/docs/invisible#auto_render
 * Renders a hidden input that is populated with the response from Recaptcha. The 'name' property, or if bound to a model the ;attribute'
 * property should the name of the field that will hold the response to be validated against the server
 * Renders a button which when submit will initiate the recaptcha
 * Registers a javascript function to populate the hidden input and submit the form
 * All of the options passed in will apply to the hidden input. Pass in 'buttonOptions' under the 'options' for that
 */
class Recaptcha2HiddenResponseInputWithSubmit extends InputWidget
{
    /**
     * @var array Options to apply to the rendered submit button
     */
    public $buttonOptions = [
        'content' => 'Submit',
    ];

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $buttonOptions = $this->buttonOptions;
        $buttonOptions['class'] = (!isset($buttonOptions['class'])) ? 'g-recaptcha' : $buttonOptions['class'].' g-recaptcha';
        $buttonOptions['data-sitekey'] = Yii::$app->params['recaptcha']['siteKey'];
        $buttonOptions['id'] = (!isset($buttonOptions['id'])) ? 'recaptcha-submit-'.uniqid() : $buttonOptions['id'];
        $buttonOptions['data-callback'] = $this->getCallbackFunctionName();

        $this->registerJavascript($buttonOptions['id']);
        $content= ArrayHelper::remove($buttonOptions, 'content');
        $buttonHtml = Html::button($content, $buttonOptions);

        return parent::renderInputHtml('hidden')."\n".$buttonHtml;
    }

    /**
     * Registers a Javascript function in the view that is intended to be the callback for a hidden Recaptcha button
     * that is bound to the submit button on the form
     * @param string $id
     * @return null
     */
    private function registerJavascript($buttonId)
    {
        // --- Load the JS file from Google
        $this->getView()->registerJsFile('https://www.google.com/recaptcha/api.js');

        // --- Create our callback to submit the form the button is rendered in
        $responseInputId = $this->options['id'];
        $callbackFunctionName = $this->getCallbackFunctionName();
        $js = <<<JAVASCRIPT
function {$callbackFunctionName}(response){
    $("#{$responseInputId}").val(response);
    $("#{$buttonId}").closest("form").submit();
}
JAVASCRIPT;

        $this->getView()->registerJs($js, View::POS_END);
    }

    /**
     * Setup a callback function for the recaptcha widget based on the name of
     * the button being pressed so it will be unique among instances on the page
     * @return string
     */
    private function getCallbackFunctionName()
    {
        return 'populate'.Inflector::camelize($this->options['id']).'AndSubmit';
    }
}

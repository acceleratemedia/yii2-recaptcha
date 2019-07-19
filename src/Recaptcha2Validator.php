<?php

namespace bvb\recaptcha;

Use yii;
use yii\validators\Validator;

/**
 * Validates a Recaptcha response using the secret key. The secret key must be set in
 * the application params under ['recaptcha']['secretKey']
 */
class Recaptcha2Validator extends Validator
{
    /**
     * {@inheritdoc}
     */
    public $skipOnEmpty = false;

    /**
     * {@inheritdoc}
     */
    public function validateAttribute($model, $attribute)
    {
        // Get Vehicle
        $ch = curl_init();

        $postFields = array(
            'secret' => Yii::$app->params['recaptcha']['secretKey'],
            'response' => $model->{$attribute}
        );

        $curlConfig = array(
            CURLOPT_URL            => 'https://www.google.com/recaptcha/api/siteverify',
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS     => $postFields
        );

        curl_setopt_array($ch, $curlConfig);
        $result_json = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result_json);
        if(!$result->success){
            $this->addError($model, $attribute, 'Validating the Recaptcha response failed. Please try again');
        }
    }
}
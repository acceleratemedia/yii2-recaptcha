<?php

namespace bvb\recaptcha;

use Yii;
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

        $postFields = [
            'secret' => Yii::$app->params['recaptcha']['secretKey'],
            'response' => $model->{$attribute}
        ];

        $curlConfig = [
            CURLOPT_URL            => 'https://www.google.com/recaptcha/api/siteverify',
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS     => $postFields
        ];

        curl_setopt_array($ch, $curlConfig);
        $result_json = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result_json);
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                Yii::info(' - No errors');
            break;
            case JSON_ERROR_DEPTH:
                Yii::info(' - Maximum stack depth exceeded');
            break;
            case JSON_ERROR_STATE_MISMATCH:
                Yii::info(' - Underflow or the modes mismatch');
            break;
            case JSON_ERROR_CTRL_CHAR:
                Yii::info(' - Unexpected control character found');
            break;
            case JSON_ERROR_SYNTAX:
                Yii::info(' - Syntax error, malformed JSON');
            break;
            case JSON_ERROR_UTF8:
                Yii::info(' - Malformed UTF-8 characters, possibly incorrectly encoded');
            break;
            default:
                Yii::info(' - Unknown error');
            break;
        }
        if(!$result->success){
            $this->addError($model, $attribute, 'Validating the Recaptcha response failed. Please try again');
        }
    }
}
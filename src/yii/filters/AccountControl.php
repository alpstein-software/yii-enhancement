<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\yii\filters;

use common\models\User;
use yii\base\ActionFilter;
use Yii;
use yii\web\Controller;

/**
 * Class AccountControl
 * @package alpstein\yii\filters
 */
class AccountControl extends ActionFilter
{

    /**
     * @param \yii\base\Action $action
     * @return bool
     * @throws
     */
    public function beforeAction($action)
    {
        $user = Yii::$app->user;
        if ($user->getIsGuest()) {
//            $user->loginRequired();
            return true;
        }

        /** @var User $identity */
        $identity = $user->getIdentity();

        //if user is logged on, but no account id, load the default one
        if (($account = Yii::$app->getAccount()) === null) {
            $this->redirectToAccount($identity->getDefaultAccountCode());
            return false;
        }

        $accounts = $identity->getAccessibleAccountCodes();
        if (!in_array($account->code, $accounts)) {
            $this->redirectToAccount($identity->getDefaultAccountCode());
            return false;
        }

        return true;
    }

    /**
     * redirect user to default account
     * @param $account
     */
    protected function redirectToAccount($account)
    {
        Yii::$app->setAccountCode($account);

        /** @var Controller $controller */
        $controller = $this->owner;
        $route = $controller->getRoute();
        $params = Yii::$app->request->getQueryParams();
        $params[0] = trim($route, '/');

        Yii::$app->getResponse()->redirect($params);
    }
}

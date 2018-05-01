<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\audit\components\web;

use alpstein\audit\Audit;
use alpstein\audit\components\Helper;
use alpstein\audit\models\AuditError;
use alpstein\yii\DateTime;
use Yii;

/**
 * Class ErrorHandler
 * @package alpstein\audit\components
 */
class ErrorHandler extends \yii\web\ErrorHandler
{
    /**
     * @var array
     */
    private $__exceptions = [];

    /**
     * @inheritdoc
     * @param \Exception $exception
     */
    public function logException($exception)
    {
        try {
            /** @var Audit $audit */
            $audit = Yii::$app->audit;
            if (!$audit) {
                throw new \Exception('Audit module cannot be loaded');
            }

            $entry = $audit->getEntry(true);
            if ($entry) {
                $this->createLog($entry->id, $exception);
                $entry->finalize();
            }
        } catch (\Exception $e) {
            // if we catch an exception here, let it slide, we don't want recursive errors killing the script
            Yii::error($e);
        }

        parent::logException($exception);
    }

    /**
     * @param int $id Entry to associate the error with
     * @param \Exception|\Throwable  $exception
     * @return bool
     */
    protected function createLog($id, $exception)
    {
        // Only log each exception once
        $exceptionId = spl_object_hash($exception);
        if (in_array($exceptionId, $this->__exceptions)) {
            return true;
        }

        // If this is a follow up exception, make sure to log the base exception first
        if ($exception->getPrevious()) {
            $this->createLog($id, $exception->getPrevious());
        }

        $error = new AuditError();
        $error->entry_id = $id;
        $error->message = $exception->getMessage();
        $error->code = $exception->getCode();
        $error->file = $exception->getFile();
        $error->line = $exception->getLine();
        $error->trace = Helper::cleanupTrace($exception->getTrace());
        $error->hash = Helper::hash($error->message . $error->file . $error->line);
        $error->created_at = DateTime::getCurrentDateTime();

        $this->__exceptions[] = $exceptionId;

        return $error->save(false) ? $error : null;
    }
}

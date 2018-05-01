<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\helpers;

use alpstein\yii\base\BaseObject;
use alpstein\yii\helpers\ArrayHelper;
use Aws\Result;
use Imagine\Exception\RuntimeException;
use Imagine\Gd\Imagine;
use yii\web\UploadedFile;
use Yii;

/**
 * Class ImageHelper
 * @package alpstein\helpers
 */
class ImageHelper extends BaseObject
{
    /**
     * @param string $src
     * @param array $options
     * @return string
     */
    public static function resolveSrc($src, $options = [])
    {
        if (!empty($src) && substr($src, 0, 3) === 's3:') {
            $host = Yii::$app->params['aws.s3.cdn'];
            return $host  . '/' . str_replace('s3:', '', $src);
        }

        $dimension = ArrayHelper::getValue($options, 'dimension', '250x250');
        return sprintf('%s/%s', 'http://via.placeholder.com', $dimension);
    }

    /**
     * ```php
     * to call resize on imagine, original:
     * $image->resize(new Box(250, 250), ImageInterface::FILTER_UNDEFINED)
     * using options:
     * $options = [
     *     'resize' => [new Box(250, 250), ImageInterface::FILTER_UNDEFINED]
     * ]
     * ```
     *
     * @param UploadedFile $file
     * @param string $path for S3 path
     * @param string $id the id for the object, e.g. $user->id
     * @param array $options
     * @return string|false
     */
    public static function upload($file, $path, $id, $options = [])
    {
        //-- default to S3 first
        return static::putIntoS3Bucket($file, $path, $id, $options);
    }

    /**
     * ```php
     * to call resize on imagine, original:
     * $image->resize(new Box(250, 250), ImageInterface::FILTER_UNDEFINED)
     * using options:
     * $options = [
     *     'resize' => [new Box(250, 250), ImageInterface::FILTER_UNDEFINED]
     * ]
     * ```
     *
     * @param UploadedFile $file
     * @param string $path for S3 path
     * @param string $id the id for the object, e.g. $user->id
     * @param array $options
     * @return string|false
     */
    public static function putIntoS3Bucket($file, $path, $id, $options = [])
    {
        if (!($file instanceof UploadedFile)) {
            return false;
        }

        try {
            $tempFile = tempnam('/tmp', '_alpstein_upload') . '.' . $file->getExtension();
            if (!$file->saveAs($tempFile)) {
                return false;
            }

            $imagine = new Imagine();
            $image = $imagine->open($tempFile);


            if (($resize = ArrayHelper::getValue($options, 'resize')) !== null) {
                //call $image->resize()
                call_user_func_array([$image, 'resize'], $resize);
                Yii::debug($resize);
            }
            $image->save($tempFile, ['jpeg_quality' => 90]);

            if (is_file($tempFile)) {
                $folder = YII_DEBUG ? 'dev' : 'prod';
                $key = $folder . '/' . trim($path, '/') . '/' . sprintf('%s_web_%s.' . $file->getExtension(), $id, time());

                $client = Yii::$app->aws->getS3Client();
                $result = $client->putObject([
                    'ACL' => 'public-read',
                    'Body' => fopen($tempFile, 'r'),
                    'Bucket' => Yii::$app->params['aws.s3.bucket'],
                    'Key' => $key,
                ]);

                Yii::debug($result);
                unlink($tempFile);

                if ($result instanceof Result && ($url = $result->get('ObjectURL')) !== null) {
                    return 's3:' . $key;
                }
            }
        } catch (RuntimeException $e) {
            Yii::error($e->getMessage());
        } catch (\Exception $e) {
            Yii::error($e);
        }

        return false;
    }
}

<?php
/**
 * Class Image
 *
 * @package Catalog\Model\Tool
 */
class ModelToolImage extends Model {
	/**
	 * Resize
	 *
	 * @param string $filename
	 * @param int    $width
	 * @param int    $height
	 * @param string $default
	 *
	 * @return string
	 * @throws \Exception
	 */
    public function resize(string $filename, int $width, int $height, string $default = ''): string {
        if (!is_file(DIR_IMAGE . $filename) || substr(str_replace('\\', '/', realpath(DIR_IMAGE . $filename)), 0, strlen(DIR_IMAGE)) != str_replace('\\', '/', DIR_IMAGE)) {
            return '';
        }

        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $image_old = $filename;
        $image_new = 'cache/' . oc_substr($filename, 0, oc_strrpos($filename, '.')) . '-' . (int)$width . 'x' . (int)$height . '.' . $extension;

        if (!is_file(DIR_IMAGE . $image_new) || (filemtime(DIR_IMAGE . $image_old) > filemtime(DIR_IMAGE . $image_new))) {
            [$width_orig, $height_orig, $image_type] = getimagesize(DIR_IMAGE . $image_old);

            if (!in_array($image_type, [IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF, IMAGETYPE_WEBP])) {
                if ($this->request->server['HTTPS']) {
                    return HTTPS_CATALOG . 'image/' . $image_old;
                } else {
                    return HTTP_CATALOG . 'image/' . $image_old;
                }
            }

            $path = '';

            $directories = explode('/', dirname($image_new));

            foreach ($directories as $directory) {
                $path = $path . '/' . $directory;

                if (!is_dir(DIR_IMAGE . $path)) {
                    @mkdir(DIR_IMAGE . $path, 0777);
                }
            }

            if ($width_orig != $width || $height_orig != $height) {
                $image = new \Image(DIR_IMAGE . $image_old);
                $image->resize($width, $height, $default);
                $image->save(DIR_IMAGE . $image_new);
            } else {
                copy(DIR_IMAGE . $image_old, DIR_IMAGE . $image_new);
            }
        }

        $image_new = str_replace(' ', '%20', $image_new);  // fix bug when attach image on email (gmail.com). it is automatic changing space " " to +

        if ($this->request->server['HTTPS']) {
            return $this->config->get('config_ssl') . 'image/' . $image_new;
        } else {
            return $this->config->get('config_url') . 'image/' . $image_new;
        }
    }
}

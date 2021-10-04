<?php

namespace ITPolice\YandexDisk;

use Carbon\Carbon;
use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\Config;

class YandexDiskAdapter extends AbstractAdapter
{
    protected $client;
    protected $pathPrefix;

    /**
     * @param \Arhitector\Yandex\Disk $client
     * @param string $pathPrefix
     */
    public function __construct(\Arhitector\Yandex\Disk $client, $pathPrefix = '/') {
        $this->client = $client;
        $this->pathPrefix = $pathPrefix;
    }

    /**
     * Write a new file.
     *
     * @param string $path
     * @param string $contents
     * @param Config $config Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function write($path, $contents, Config $config)
    {
        return $this->upload($path, $contents, $config);
    }

    /**
     * Write a new file using a stream.
     *
     * @param string $path
     * @param resource $resource
     * @param Config $config Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function writeStream($path, $resource, Config $config)
    {
        // TODO: Implement writeStream() method.
    }

    /**
     * Update a file.
     *
     * @param string $path
     * @param string $contents
     * @param Config $config Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function update($path, $contents, Config $config)
    {
        return $this->upload($path, $contents, $config, true);
    }

    /**
     * Update a file using a stream.
     *
     * @param string $path
     * @param resource $resource
     * @param Config $config Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function updateStream($path, $resource, Config $config)
    {
        // TODO: Implement updateStream() method.
    }

    /**
     * Rename a file.
     *
     * @param string $path
     * @param string $newpath
     *
     * @return bool
     */
    public function rename($path, $newpath)
    {
        $location = $this->applyPathPrefix($path);
        $resource = $this->client->getResource($location);
        if (!$resource->has()) {
            return false;
        }

        $locationCopy = $this->applyPathPrefix($newpath);
        $resource2 = $this->client->getResource($locationCopy);
        return $resource->move($resource2);
    }

    /**
     * Copy a file.
     *
     * @param string $path
     * @param string $newpath
     *
     * @return bool
     */
    public function copy($path, $newpath)
    {
        $location = $this->applyPathPrefix($path);
        $resource = $this->client->getResource($location);
        if (!$resource->has()) {
            return false;
        }

        $uploadDir = $this->applyPathPrefix(dirname($newpath));
        $this->createDirRecursive($uploadDir);

        $locationCopy = $this->applyPathPrefix($newpath);
        $resource2 = $this->client->getResource($locationCopy);
        return $resource->copy($resource2);
    }

    /**
     * Delete a file.
     *
     * @param string $path
     *
     * @return bool
     */
    public function delete($path)
    {
        $location = $this->applyPathPrefix($path);
        $resource = $this->client->getResource($location);
        return $resource->delete(true);
    }

    /**
     * Delete a directory.
     *
     * @param string $dirname
     *
     * @return bool
     */
    public function deleteDir($dirname)
    {
        $location = $this->applyPathPrefix($dirname);
        $resource = $this->client->getResource($location);
        if (!$resource->has() || !$resource->isDir()) {
            return false;
        }
        return $resource->delete(true);
    }

    /**
     * Create a directory.
     *
     * @param string $dirname directory name
     * @param Config $config
     *
     * @return array|false
     */
    public function createDir($dirname, Config $config)
    {
        $uploadDir = $this->applyPathPrefix($dirname);
        return $this->createDirRecursive($uploadDir);
    }

    /**
     * Set the visibility for a file.
     *
     * @param string $path
     * @param string $visibility
     *
     * @return array|false file meta data
     */
    public function setVisibility($path, $visibility)
    {
        // todo fake
        $visibility = 'public';
        return compact('path', 'visibility');
    }

    /**
     * Check whether a file exists.
     *
     * @param string $path
     *
     * @return array|bool|null
     */
    public function has($path)
    {
        $location = $this->applyPathPrefix($path);
        $resource = $this->client->getResource($location);
        return $resource->has();
    }

    /**
     * Read a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function read($path)
    {
        $location = $this->applyPathPrefix($path);
        $resource = $this->client->getResource($location);

        $fileUrl = $resource->getLink();
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL            => $fileUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'GET',
        ));
        $stream = curl_exec($curl);
        curl_close($curl);

        return ['type' => 'file', 'path' => $path, 'contents' => $stream];
    }

    /**
     * Read a file as a stream.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function readStream($path)
    {
        // TODO: Implement readStream() method.
    }

    /**
     * List contents of a directory.
     *
     * @param string $directory
     * @param bool $recursive
     *
     * @return array
     */
    public function listContents($directory = '', $recursive = false)
    {
        $location = $this->applyPathPrefix($directory);
        $resource = $this->client->getResource($location, 1000000);

        $files = [];
        if ($resource->has() && $resource->isDir()) {
            foreach ($resource->items as $item) {
                /**
                 * @var \Arhitector\Yandex\Disk\Resource\Closed $item
                 */
                $files[] = [
                    'type' => $item->isFile() ? 'file' : 'dir',
                    'path' => $directory.'/'.$item->name
                ];

                if($item->isDir() && $recursive) {
                    $files = array_merge_recursive($files, $this->listContents($directory.'/'.$item->name, $recursive));
                }
            }
        }
        return $files;
    }

    /**
     * Get all the meta data of a file or directory.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getMetadata($path)
    {
        $location = $this->applyPathPrefix($path);
        $resource = $this->client->getResource($location);
        if(!$resource->has()) return false;
        return [
            'path' => $path,
            'timestamp' => Carbon::parse($resource->modified)->timestamp,
            'size' => $resource->size,
            'mimetype' => $resource->mime_type
        ];
    }

    /**
     * Get the size of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getSize($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * Get the mimetype of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getMimetype($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * Get the last modified time of a file as a timestamp.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getTimestamp($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * Get the visibility of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getVisibility($path)
    {
        // todo fake
        $visibility = 'public';
        return compact('path', 'visibility');
    }

    protected function createTmpFile($content) {
        $tmpPath = __DIR__.'/'.uniqid();
        file_put_contents($tmpPath, $content);
        return $tmpPath;
    }

    protected function deleteTmpFile($tmpPath) {
        @unlink($tmpPath);
    }

    protected function createDirRecursive($path) {
        $dirs = array_filter(explode('/', $path));
        $currentDir = '/';
        foreach ($dirs as $dir) {
            $currentDir .= $dir . '/';
            $dir = $this->client->getResource($currentDir, 10000);
            if (!$dir->has()) $dir->create();
        }

        return true;
    }
    /**
     * Upload a file.
     *
     * @param string $path
     * @param string $contents
     * @param Config $config Config object
     * @param boolean $overwrite
     *
     * @return array|false false on failure file meta data on success
     */
    protected function upload($path, $contents, $config, $overwrite = false) {
        $uploadDir = $this->applyPathPrefix(dirname($path));
        $this->createDirRecursive($uploadDir);
        $tmpPath = $this->createTmpFile($contents);
        $resource = $this->client->getResource($this->applyPathPrefix($path));
        $res = $resource->upload($tmpPath, $overwrite, false);
        $size = filesize($tmpPath);
        $this->deleteTmpFile($tmpPath);

        if(!$res) return false;

        $type = 'file';
        $result = compact('contents', 'type', 'size', 'path');

        if ($visibility = $config->get('visibility')) {
            $result['visibility'] = $visibility;
            $this->setVisibility($path, $visibility);
        }
        return $result;
    }

}

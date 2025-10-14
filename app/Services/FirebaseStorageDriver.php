<?php

namespace App\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Kreait\Firebase\Storage as FirebaseStorage;

class FirebaseStorageDriver implements Filesystem
{
    protected $storage;
    protected $bucket;

    public function __construct(FirebaseStorage $storage, $bucket)
    {
        $this->storage = $storage;
        $this->bucket = $storage->getBucket($bucket);
    }

    public function exists($path)
    {
        try {
            $this->bucket->object($path)->exists();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function get($path)
    {
        try {
            return $this->bucket->object($path)->downloadAsString();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function put($path, $contents, $options = [])
    {
        try {
            $this->bucket->upload($contents, [
                'name' => $path,
                'metadata' => $options
            ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function putFile($path, $file, $options = [])
    {
        try {
            $contents = is_resource($file) ? stream_get_contents($file) : file_get_contents($file);
            return $this->put($path, $contents, $options);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function putFileAs($path, $file, $name, $options = [])
    {
        return $this->putFile($path . '/' . $name, $file, $options);
    }

    public function delete($paths)
    {
        $paths = is_array($paths) ? $paths : func_get_args();
        
        foreach ($paths as $path) {
            try {
                $this->bucket->object($path)->delete();
            } catch (\Exception $e) {
                return false;
            }
        }
        
        return true;
    }

    public function copy($from, $to)
    {
        try {
            $content = $this->get($from);
            return $this->put($to, $content);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function move($from, $to)
    {
        if ($this->copy($from, $to)) {
            return $this->delete($from);
        }
        return false;
    }

    public function size($path)
    {
        try {
            return $this->bucket->object($path)->info()['size'];
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function lastModified($path)
    {
        try {
            $info = $this->bucket->object($path)->info();
            return strtotime($info['updated']);
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function files($directory = null, $recursive = false)
    {
        try {
            $objects = $this->bucket->objects(['prefix' => $directory]);
            $files = [];
            foreach ($objects as $object) {
                $files[] = $object->name();
            }
            return $files;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function allFiles($directory = null)
    {
        return $this->files($directory, true);
    }

    public function directories($directory = null, $recursive = false)
    {
        // Firebase Storage doesn't have directories concept
        return [];
    }

    public function allDirectories($directory = null)
    {
        return [];
    }

    public function makeDirectory($path)
    {
        // Firebase Storage doesn't need directory creation
        return true;
    }

    public function deleteDirectory($directory)
    {
        try {
            $objects = $this->bucket->objects(['prefix' => $directory]);
            foreach ($objects as $object) {
                $object->delete();
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function url($path)
    {
        try {
            return $this->bucket->object($path)->signedUrl(new \DateTime('+1 hour'));
        } catch (\Exception $e) {
            return null;
        }
    }

    public function temporaryUrl($path, $expiration, array $options = [])
    {
        try {
            return $this->bucket->object($path)->signedUrl($expiration, $options);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function visibility($path)
    {
        return 'public'; // Firebase Storage objects are public by default
    }

    public function setVisibility($path, $visibility)
    {
        return true; // Firebase Storage handles visibility automatically
    }

    public function prepend($path, $data)
    {
        $existing = $this->get($path) ?: '';
        return $this->put($path, $data . $existing);
    }

    public function append($path, $data)
    {
        $existing = $this->get($path) ?: '';
        return $this->put($path, $existing . $data);
    }
}
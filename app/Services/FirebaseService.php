<?php

namespace App\Services;

use Kreait\Firebase\Auth;
use Kreait\Firebase\Database;
use Kreait\Firebase\Firestore;
use Kreait\Firebase\Storage;
use Kreait\Firebase\Messaging;

class FirebaseService
{
    protected $auth;
    protected $database;
    protected $firestore;
    protected $storage;
    protected $messaging;

    public function __construct()
    {
        $this->auth = app('firebase.auth');
        $this->database = app('firebase.database');
        $this->firestore = app('firebase.firestore');
        $this->storage = app('firebase.storage');
        $this->messaging = app('firebase.messaging');
    }

    /**
     * Get Firebase Auth instance
     */
    public function auth(): Auth
    {
        return $this->auth;
    }

    /**
     * Get Firebase Realtime Database instance
     */
    public function database(): Database
    {
        return $this->database;
    }

    /**
     * Get Firebase Firestore instance
     */
    public function firestore(): Firestore
    {
        return $this->firestore;
    }

    /**
     * Get Firebase Storage instance
     */
    public function storage(): Storage
    {
        return $this->storage;
    }

    /**
     * Get Firebase Messaging instance
     */
    public function messaging(): Messaging
    {
        return $this->messaging;
    }

    /**
     * Create a new user in Firebase Auth
     */
    public function createUser(array $properties)
    {
        return $this->auth->createUser($properties);
    }

    /**
     * Get user by UID
     */
    public function getUser(string $uid)
    {
        return $this->auth->getUser($uid);
    }

    /**
     * Update user in Firebase Auth
     */
    public function updateUser(string $uid, array $properties)
    {
        return $this->auth->updateUser($uid, $properties);
    }

    /**
     * Delete user from Firebase Auth
     */
    public function deleteUser(string $uid)
    {
        return $this->auth->deleteUser($uid);
    }

    /**
     * Store data in Firestore
     */
    public function storeDocument(string $collection, string $document, array $data)
    {
        return $this->firestore->database()->collection($collection)->document($document)->set($data);
    }

    /**
     * Get document from Firestore
     */
    public function getDocument(string $collection, string $document)
    {
        return $this->firestore->database()->collection($collection)->document($document)->snapshot();
    }

    /**
     * Upload file to Firebase Storage
     */
    public function uploadFile(string $path, $file)
    {
        $bucket = $this->storage->getBucket();
        return $bucket->upload($file, ['name' => $path]);
    }

    /**
     * Get Firebase Storage URL for a file
     */
    public function getStorageUrl(string $path)
    {
        $bucket = $this->storage->getBucket();
        $object = $bucket->object($path);
        
        // Generate a signed URL that expires in 1 hour
        return $object->signedUrl(new \DateTime('+1 hour'));
    }

    /**
     * Send push notification
     */
    public function sendNotification(array $message)
    {
        return $this->messaging->send($message);
    }
}
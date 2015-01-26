<?php namespace Immocaster\Data;

interface TokenRepositoryInterface{

    /**
     * Load and return the application token
     *
     * @param string $username
     * @return string
     **/
    public function getApplicationToken($username);

    /**
     * Persists the application token
     *
     * @param string $token
     * @param string $secret
     * @param string $user
     * @return bool
     **/
    public function saveApplicationToken($token, $secret, $user);

    /**
     * Load and return a requesttoken without a session
     *
     * @return string
     **/
    public function getRequestTokenWithoutSession();

    /**
     * Load and return request token
     *
     * @param string $token (optional)
     * @return object
     **/
    public function getRequestToken($token=null);

    /**
     * Save the request token
     *
     * @param string $token The OAuth token
     * @param string $secret The matching secret
     * @return bool
     **/
    public function saveRequestToken($token, $secret);

    /**
     * Return all access tokens
     *
     * @return array
     **/
    public function getAllApplicationUsers();

}
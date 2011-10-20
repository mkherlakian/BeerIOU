<?php

class Application_Model_CurrentUser
    extends Application_Model_User
{
    public function __sleep()
    {
        return array('_userId', '_email', '_firstName', '_lastName', '_registered', '_favoriteBeerId');
    }
}
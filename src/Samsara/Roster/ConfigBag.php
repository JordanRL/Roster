<?php


namespace Samsara\Roster;


use Noodlehaus\Config;

class ConfigBag
{

    private static Config $rosterConfig;
    private static Config $applicationConfig;
    private static Config $mkdocsConfig;

    /**
     * @return Config
     */
    public static function getApplicationConfig(): Config
    {
        return self::$applicationConfig;
    }

    /**
     * @return Config
     */
    public static function getMkdocsConfig(): Config
    {
        return self::$mkdocsConfig;
    }

    /**
     * @return Config
     */
    public static function getRosterConfig(): Config
    {
        return self::$rosterConfig;
    }

    /**
     * @param Config $applicationConfig
     */
    public static function setApplicationConfig(Config $applicationConfig): void
    {
        self::$applicationConfig = $applicationConfig;
    }

    /**
     * @param Config $mkdocsConfig
     */
    public static function setMkdocsConfig(Config $mkdocsConfig): void
    {
        self::$mkdocsConfig = $mkdocsConfig;
    }

    /**
     * @param Config $rosterConfig
     */
    public static function setRosterConfig(Config $rosterConfig): void
    {
        self::$rosterConfig = $rosterConfig;
    }

}
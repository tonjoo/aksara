<?php

namespace Aksara\PluginRegistry;

interface PluginRegistryHandler
{
    public function getRegisteredPlugins();
    public function getActivePlugins();
    public function isActive($name);
    public function isRegistered($name);
    public function activatePlugin($name);
    public function deactivatePlugin($name);
    public function getPluginRoot();
}
<?php
use Plugin\User\Models\UserMeta;

// Function for delete user meta data
function delete_user_meta($userID = false, $key = false)
{
    // To function delete_options in user meta model
    $user_meta = UserMeta::delete_user_meta($userID, $key);
    return $user_meta;
}

// Function for get user meta data
function get_user_meta($userID = false, $key = false, $unserialize = false)
{
    // To function get_user_meta in user meta model
    $user_meta = UserMeta::get_user_meta($userID, $key, $unserialize);
    return $user_meta;
}

// Function for setting user meta data
function set_user_meta($userID = false, $key = false, $value = false, $serialize = false)
{
    // To function set_user_meta in user meta model
    $user_meta = UserMeta::set_user_meta($userID, $key, $value, $serialize);
    return $user_meta;
}

function add_capability($name, $id = false, $parent = false, $callback = null)
{
    return RoleCapability::add($name, $id, $parent, $callback);
}

function get_capability($id = false)
{
    return RoleCapability::get($id);
}

function get_capabilities()
{
    return RoleCapability::all();
}

function has_capability($capabilities)
{
    return \UserCapability::hasAny($capabilities);
}

function authorize($capabilities)
{
    $capabilityStr = '';
    if (!has_capability($capabilities)) {
        if (is_array($capabilities)) {
            $capabilityStrs = [];
            foreach ($capabilities as $key => $capability) {
                if (is_array($capability)) {
                    $capabilityStrs[] = $key;
                } else {
                    $capabilityStrs[] = $capability;
                }
            }
            $capabilityStr = implode('/', $capabilityStrs);
        } else {
            $capabilityStr = $capabilities;
        }
        abort(403, "Does not have any $capabilityStr access");
    }
}

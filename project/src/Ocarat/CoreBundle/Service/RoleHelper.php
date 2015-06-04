<?php
namespace Ocarat\CoreBundle\Service;

class RoleHelper
{
    private $rolesHierarchy;

    public function __construct($rolesHierarchy)
    {
        $this->rolesHierarchy = $rolesHierarchy;
    }

    public function getRoles()
    {
        $roles = array();

        foreach($this->rolesHierarchy as $key => $role) {
            $roles[$key] = $key;
        };

        return array_unique($roles);
    }
}
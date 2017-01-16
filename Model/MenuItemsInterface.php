<?php
namespace SuttonSilver\CMSMenu\Model;
interface MenuItemsInterface 
{
    const ID                            = 'suttonsilver_cmsmenu_menuitems_id';
    const SLUG                          = 'slug';
    const TITLE                         = 'title';
    const PARENT                        = 'parent_id';
    const PATH                          = 'path';
    const LEVEL                         = 'level';
    const CREATION_TIME                 = 'creation_time';
    const UPDATE_TIME                   = 'update_time';
    const POSITION                      = 'position';
    const SORT_ORDER                    = 'sort_order';
    const IS_ACTIVE                     = 'is_active';


    public function getId();
    public function setId($id);

    public function getStoreId();
    public function setStoreId($storeId);

    public function getSlug();
    public function setSlug($slug);

    public function getTitle();
    public function setTitle($title);

    public function getPath();
    public function setPath($path);

    public function getParentId();
    public function setParentId($parent);

    public function getlevel();
    public function setLevel($level);

    public function getCreatedAt();
    public function setCreatedAt($creationTime);

    public function getUpdatedAt();
    public function setUpdatedAt($updateTime);

    public function getSortOrder();
    public function setSortOrder($sortOrder);

    public function getPosition();
    public function setPosition($position);

    public function getIsActive();
    public function setIsActive($isActive);







}
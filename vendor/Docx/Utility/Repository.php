<?php

/*
 * This file is part of Docx.
 *
 * Copyright (c) 2014 MIT License
 */

namespace Docx\Utility;

use Docx\Common;
use TQ\Git\Repository\Repository as GitRepository;
use TQ\Git\Cli\Binary as GitBinary;


/**
 * Git仓库
 *
 * @author Ryan Liu <azhai@126.com>
 */
class Repository extends GitRepository
{
    public static function open($repositoryPath, $git = null,
            $createIfNotExists = false, $initArguments = null,
            $findRepositoryRoot = true)
    {
        if (empty($git)) {
            $git = GitBinary::locateBinary() ?: 'git';
        }
        return parent::open($repositoryPath, $git, $createIfNotExists,
                            $initArguments, $findRepositoryRoot);
    }
    
    public static function create($repositoryPath, $remotePath,
            $git = null, $initArguments = null, $findRepositoryRoot = true)
    {
        $repo = self::open($repositoryPath, $git, true,
                            $initArguments, $findRepositoryRoot);
        $repo->remote('add', 'origin', $remotePath);
        return $repo;
    }
    
    public function commitMutely($commitMsg, array $file = null,
                            $author = null, array $extraArgs = [])
    {
        try {
            $this->commit($commitMsg, $file, $author, $extraArgs);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function __call($name, $args)
    {
        $git = $this->getGit();
        $path = $this->getRepositoryPath();
        return $git->$name($path, $args);
    }
}
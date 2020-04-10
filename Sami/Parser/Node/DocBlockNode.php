<?php

/*
 * This file is part of the Sami utility.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sami\Parser\Node;

class DocBlockNode
{
    protected $shortDesc;
    protected $longDesc;
    protected $tags = array();
    protected $errors = array();

    public function setParamHierarchy()
    {
        $params = isset($this->tags['param'])
            ? $this->tags['param']
            : [];

        $prevTopKey = null;
        foreach ($params as $i => $val) {
            if (strpos($val[1], '.') === false) {
                $prevTopKey = $i;
                continue;
            }

            if ($prevTopKey === null) {
                throw new \Exception('child param cannot be first');
            }

            $parts = explode('.', $val[1]);
            $prevName = $params[$prevTopKey][1];

            if ($parts[0] !== $prevName) {
                throw new \Exception('child param does not descend from previous parent.');
            }

            $params[$prevTopKey][] = $val;
            unset($params[$i]);
        }

        $this->tags['param'] = array_values($params);
    }

    public function addTag($key, $value)
    {
        $this->tags[$key][] = $value;
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function getOtherTags()
    {
        $tags = $this->tags;
        unset($tags['param'], $tags['return'], $tags['var'], $tags['throws']);

        foreach ($tags as $name => $values) {
            foreach ($values as $i => $value) {
                // For 'see' tag we try to maintain backwards compatibility
                // by returning only a part of the value.
                if ($name === 'see') {
                    $value = $value[0];
                }

                $tags[$name][$i] = is_string($value) ? explode(' ', $value) : $value;
            }
        }

        return $tags;
    }

    public function getTag($key)
    {
        return $this->tags[$key] ?? array();
    }

    public function getShortDesc()
    {
        return $this->shortDesc;
    }

    public function getLongDesc()
    {
        return $this->longDesc;
    }

    public function setShortDesc($shortDesc)
    {
        $this->shortDesc = $shortDesc;
    }

    public function setLongDesc($longDesc)
    {
        $this->longDesc = $longDesc;
    }

    public function getDesc()
    {
        return $this->shortDesc."\n\n".$this->longDesc;
    }

    public function addError($error)
    {
        $this->errors[] = $error;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}

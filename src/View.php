<?php

namespace Isholao\Template;

class View implements ViewInterface
{

    //String of template file
    private $_file = NULL;
    //Array of view section
    private $_blocks = [];
    //Container for capture content for a section
    private $_capture = [];
    private $_dir = [];
    private $_closures = [];

    /**
     * Set search directories for the view
     *
     * @param  array $dirs
     * @return View
     */
    public function setDirectories(array $dirs): ViewInterface
    {
        foreach ($dirs as $dir)
        {
            $this->setDirectory($dir);
        }
        return $this;
    }

    /**
     * Set search directory for the view
     *
     * @param  string $dir
     * @return View
     */
    public function setDirectory(string $dir): ViewInterface
    {
        \clearstatcache(TRUE);
        if (!\is_dir($clean_dir = \realpath($dir)))
        {
            throw new \Error("Invalid directory path - {$clean_dir}.",
                             E_USER_WARNING);
        }

        $this->_dir[$clean_dir] = TRUE;
        return $this;
    }

    /**
     * Set data
     *
     * @param  string $key
     * @param  mixed $value
     * @return View
     */
    public function setData(string $key, $value): ViewInterface
    {
        $this->{$key} = $value;
        return $this;
    }

    /**
     * 
     * @param string $name
     * @param mixed $value
     */
    public function __set(string $name, $value)
    {
        if ($value instanceof \Closure)
        {
            $this->_closures[\strtolower($name)] = $value->bindTo($this);
        } else
        {
            $this->{$name} = $value;
        }
    }

    /**
     * 
     * @param string $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return isset($this->{$name});
    }

    /**
     * 
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        if (isset($this->{$name}))
        {
            return $this->{$name};
        }
    }

    /**
     * 
     * @param array $data
     * @return ViewInterface
     */
    public function populate(array $data): ViewInterface
    {
        foreach ($data as $key => &$value)
        {
            $this->{$key} = $value;
        }
        return $this;
    }

    /**
     * 
     * @param string $file
     * @return string
     */
    private function getFile(string $file)
    {
        if (\strrpos($file, '.php') !== (\strlen($file) - \strlen('.php')))
        {
            $file .= '.php';
        }

        if (\file_exists($file))
        {
            return $file;
        }

        foreach ($this->_dir as $dir => $value)
        {
            if (\file_exists($file_path = $dir . '/' . $file))
            {
                return $file_path;
            }
        }
        throw new \Error("Error rendering file : $file", E_USER_ERROR);
    }

    /**
     * Set the view to render
     *
     * @param  string $file filename of the view to render
     * @return ViewInterface
     */
    public function setView(string $file): ViewInterface
    {
        //set the file
        $this->_file = $this->getFile($file);
        return $this;
    }

    /**
     * Returns the output of a parsed template as a string.
     *
     * @return string Content of parsed template.
     */
    public function render(): string
    {
        if (\is_NULL($this->_file))
        {
            throw new \Error('View file is not yet set.', E_USER_ERROR);
        }
        //begin loop
        while (TRUE)
        {
            //start buffering
            \ob_start();
            //require the file
            require $this->_file;
            //is layout file set ?
            if (!isset($this->_parent))
            {
                //return the content;
                return \ob_get_clean();
            } else
            {
                //get and set the content from buffer and clean buffer
                $this->_content = \ob_get_clean();
                //set the layout as the file
                $this->_file = $this->_parent;
                //unset layout
                unset($this->_parent);
            }
        }
    }

    /**
     * Get the main content of the view after processing
     *
     * @return string
     */
    function getContent(): string
    {
        return isset($this->_content) ? $this->_content : '';
    }

    /**
     * Check if the view has a parent layout
     *
     * @return bool
     */
    function hasParent(): bool
    {
        return isset($this->_parent);
    }

    /**
     * Get parent layout
     *
     * @return string filename of the parent layout
     */
    function getParent(): string
    {
        return $this->_parent;
    }

    /**
     * set parent layout
     *
     * @param string $parent
     */
    function setParent(string $parent): ViewInterface
    {
        $this->_parent = $this->getFile($parent);
        return $this;
    }

    /**
     * Get all registered blocks
     *
     * @return array
     */
    function getBlocks(): array
    {
        return $this->_blocks;
    }

    /**
     * Is a particular named block available?
     *
     * @param string $name the block name.
     *
     * @return bool
     */
    public function hasBlock(string $name): bool
    {
        return isset($this->_blocks[$name]);
    }

    /**
     * Get a named block.
     *
     * @param string $name
     *
     * @return string
     */
    public function getBlock(string $name): string
    {
        return $this->_blocks[$name] ?? '';
    }

    /**
     * Set a body for a named block.
     *
     * @param string $name
     * @param string $body
     */
    private function setBlock(string $name, string $body)
    {
        $this->_blocks[$name] = $body;
        return $this;
    }

    /**
     * Begin a named block.
     *
     * @param string $name
     */
    public function beginBlock(string $name): ViewInterface
    {
        $this->_capture[] = $name;
        \ob_start();
    }

    /**
     * End a named block.
     */
    public function endBlock(): void
    {
        //end buffering
        $body = \ob_get_clean();
        //get the last content that was captured
        $name = \array_pop($this->_capture);

        $content = NULL;
        //set the named block
        if (isset($this->_blocks[$name]))
        {
            $content = $this->_blocks[$name] . $body ? $body : '';
        } else
        {
            $content = $body;
        }

        $this->setBlock($name, $content);
    }

    /**
     * 
     * @param string $file
     * @param string $dir
     * @param array $data
     */
    public function partial(string $file, string $dir = NULL, array $data = []): string
    {
        $view = new self;
        $view->setView($file);
        if (!\is_null($dir))
        {
            $view->setDirectory($dir);
        }
        $view->populate($data);
        echo $view->render();
    }

    /**
     * 
     * @param string $name
     * @param array $arguments
     */
    function __call(string $name, array $arguments)
    {
        if (isset($this->_closures[$closure = \strtolower($name)]))
        {
            return \call_user_func_array($this->_closures[$closure], $arguments);
        }
        throw new \Error('Could not call ' . $name . ' on ' . \get_class());
    }

}

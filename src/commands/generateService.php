<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class generateService extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'generate:service';
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Generate Entity, Repository and Service with all files';
    // Created Directory list saved to show the developer what needs adding to composer classmap
    protected $createdDir = array();
    // All the variables needed to generate our files
    protected $replace = array();
    // Will contain the array of files that need creating
    protected $files;
    // Will contain information about paths to generate files
    protected $dir;
    
	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
        // Lets make sure we have all the information needed
        $name = $this->getConfig();
        
        // Generate some arrays containing paths and files
        $this->setFiles($name);
        
        // Generate these files
        $this->createFiles($files, $name, $dir, $replace);
        
        // Generate return message
        $this->returnMessage(ucfirst($name));
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
            array('name', InputArgument::OPTIONAL, 'Table name to Generate Files From'),
            array('table', InputArgument::OPTIONAL, 'Table name to Generate Files From'),
            array('primaryKey', InputArgument::OPTIONAL, 'Primary key to use in Entity'),
            array('timestamps', InputArgument::OPTIONAL, 'Use timestamps in Entity'),
            array('entities', InputArgument::OPTIONAL, 'Path for Entity'),
            array('repos', InputArgument::OPTIONAL, 'Path for Repository'),
            array('services', InputArgument::OPTIONAL, 'Path for Service'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
            array('no-entity', null, InputOption::VALUE_NONE, 'Skip generating an Entity.', null),
            array('no-repo', null, InputOption::VALUE_NONE, 'Skip generating an Repository.', null),
			array('no-service', null, InputOption::VALUE_NONE, 'Skip generating an Service.', null),
		);
	}
    
    /**
    * Make sure all config values are set, if not prompt the developer for them
    * 
    * @return string $name
    */
    protected function getConfig()
    {
        // Try and find needed config from optional arguements
        $table = $this->argument('table');
        $Name = $this->argument('name');
        $primaryKey = $this->argument('primaryKey');
        $timestamps = $this->argument('timestamps');
        
        // No table found, better ask for it!
        if (!$table)
        {
            $table = $this->ask('What table are you generating this for?');
        }
        
        // No name? better ask before setting the uppercase and lowercase version
        if (!$Name)
        {
            // Might as well try and guess this from the table
            $Name = $this->ask('What short name would you like for your Service? [' . ucfirst(camel_case($table)) . ']', ucfirst(camel_case($table)));
            $Name = ucfirst($Name);
        }
        $name = lcfirst($Name);
        
        // The rest of this stuff isn't needed if we are skipping the entity
        if (!$this->option('no-entity'))
        {
            // Better check if they want anything different for primary key
            if (!$primaryKey)
            {
                $primaryKey = $this->ask('and table primary key? [id]', 'id');
            }
            
            // Timestamps are good to check to
            if (!$timestamps)
            {
                $timestamps = $this->ask('and timestamps? [true]', true);
            }
            // Lets make sure their answer was true or false
            if ($timestamps && $timestamps != 'false')
            {
                $timestamps = 'true';
            }
            else
            {
                $timestamps = 'false';
            }
        }
        
        // Now lets load up the array that will be used to generate our files
        $this->replace = array(
            'name' => $name, 
            'Name' => $Name,
            'table' => $table, 
            'primaryKey' => $primaryKey, 
            'timestamps' => $timestamps,
        );
        
        // And since our app uses the name so much, lets return it for convience
        return $name;
    }
    
    /**
    * Set files to be generated and the paths to where they belong
    * 
    * @param string $name
    */
    protected function setFiles($name) 
    {
        // Lets start by creating the needed arrays
        // Files will contain all the files that need generating and their information
        $files = array();
        // Dir will contain all directory paths
        $dir = array('models' => app_path() . '/models');
        
        // Check to see if we are generating an entity
        if (!$this->option('no-entity'))
        {
            // Details for our entitiy
            $files[] = array(
                'template' => 'entity.php', 
                'type' => 'entities',
                'name' => $name . '.php',
            ); 
            // And where this should be stored
            $dir['entities'] = $this->getDir('entities', Config::get('service-generator::entitiesPath', $dir['models'] . '/entities'));
        }
        
        // Check to see if we are generating a repository
        if (!$this->option('no-repo'))
        {
            // A few files to generate for our repository
            $files[] = array(
                'template' => 'repository.php',
                'type' => 'repos',
                'name' => $name . 'Repository.php',
            ); 
            $files[] = array(
                'template' => 'repositoryInterface.php',
                'type' => 'repos',
                'name' => $name . 'Interface.php',
            ); 
            $files[] = array(
                'template' => 'repositoryServiceProvider.php',
                'type' => 'repos',
                'name' => $name . 'RepositoryServiceProvider.php',
            ); 
            // And what directory these should go in
            $dir['repos'] = $this->getDir('repos', Config::get('service-generator::reposPath', $dir['models'] . '/repositories'));
        }
        // Check to see if we are generating a service
        if (!$this->option('no-service'))
        {
            // Another set of files just for our service
            $files[] = array(
                'template' => 'service.php',
                'type' => 'services',
                'name' => $name . 'Service.php'
            ); 
            $files[] = array(
                'template' => 'serviceFacade.php',
                'type' => 'services',
                'name' => $name . 'Facade.php',
            ); 
            $files[] = array(
                'template' => 'serviceServiceProvider.php',
                'type' => 'services',
                'name' => $name . 'ServiceServiceProvider.php',
            );
            
            // And what directory these should go in
            $dir['services'] = $this->getDir('services', Config::get('service-generator::servicesPath', $dir['models'] . '/services'));
        }
        
        // Lets make these arrays available in the class
        $this->dir = $dir;
        $this->files = $files;
    }
    
    /**
    * Find the correct path, if default doesn't exist, prompt developer for it
    * 
    * @param string $type
    * @param string $default
    * @param boolean $prompt
    * @return string $path
    */
    protected function getDir($type, $default, $prompt = true)
    {
        // Check if they have set the path, use this as default if set
        if ($this->argument($type))
        {
            $default = $this->argument($type);
        }
        
        // Check if default exists, if so use it
        if (File::isDirectory($default))
        {
            return $default;
        }
        // If the default directory doesn't exist prompt unless prompt is false
        elseif ($prompt)
        {
            $dir = $this->ask('Where would you like to generate your ' . $type .'?', $default);
        }
        // If prompt is false assume we want to create the default folder
        else
        {
            $dir = $default;
        }
        
        // Create default folder
        $this->createDir($dir);
        
        // Return created directory
        return $dir;
    }
    
    /**
    * generate the files in their respective places
    * 
    * @param array $files
    * @param mixed $name
    * @param mixed $dir
    * @param mixed $replaceWord
    */
    protected function createFiles()
    {
        // create necessary variables
        $name = $this->replace['name'];
        $find = array();
        $replace = array();
        
        // Seperate the replace by keys and values, keys will be replaced by values in the generated files
        foreach ($this->replace as $key => $value)
        {
            $find[] = '%' . $key . '%';
            $replace[] = $value;
        }
        
        // Loop through files to generate, loading the content and then attempting to create them
        foreach ($this->files as $file)
        {
            // Generate content based on template
            $content = File::get(app_path() . '/commands/templates/' . $file['template']);
            $content = str_replace($find, $replace, $content);
            
            // If entity assume they want to go down a level
            if ($file['type'] == 'entities')
            {
                $path = $this->dir[$file['type']] . '/' . $file['name'];
                $created = File::put($path, $content);
            }
            else
            {
                $path = $this->dir[$file['type']] . '/' . $name;
                $this->createDir($path);
                
                $path .= '/' . $file['name'];
                $created = File::put($path, $content);
            }
            
            // Report error if was not created
            if (!$created)
            {
                $this->error('File: ' . $path . ' Could not be created');
            }
        }
    }
    
    /**
    * Checks if directory was there, if not created, if failed report error.
    * This also tracks what has been created to add to the autoload
    * 
    * @param mixed $dir
    */
    protected function createDir($dir)
    {
        // Does directory already exist?
        if (!File::isDirectory($dir))
        {
            // If doesn't exit can we create it?
            if (!File::makeDirectory($dir))
            {
                // Can't create, better tell the world!
                $this->error('Could not create ' . $dir . ' Folder');
                
                return false;
            }
            else
            {
                // Lets remember this for to prompt user to add it to an autoloader
                $this->createdDir[] = $dir;
            }
        }
        
        return true;
    }
    
    /**
    * Generate the message returned upon completion, this prompts the developer to add 
    * the needed lines to their composer.json and app/config/app.php
    * 
    * @param mixed $Name
    */
    protected function returnMessage($Name)
    {
        $message = "\n\n\n\nFiles generation complete.";
        // Are there any folders to add to composer.json?
        if (count($this->createdDir))
        {
            $message .= "\n\nPlease make sure the following files are in your composer.json autoload classmap:\n";
            $substr = strlen(base_path()) + 1;
            foreach ($this->createdDir as $dir)
            {
                $message .= '"' . substr($dir, $substr) . '"' . ",\n";
            }
            $message = substr($message, 0, -3);
            
            $message .= "\n\n Once this is done please run 'composer dump-autload";
        }
        else
        {
            // If not lets refresh their autoload to be helpful
            $this->call('dump-autoload', array());
        }
        
        // Informs developer to add the various service providers and Facades
        $message .= "\n\n Please add the following into your app/config/app.php: \n";
        if (!$this->option('no-repo'))
        {
            $message .= "'Repositories\\" . $Name . "\\" . $Name . "RepositoryServiceProvider',\n"; 
        }
        if (!$this->option('no-service'))
        {
            $message .= "'Services\\" . $Name . "\\" . $Name . "ServiceServiceProvider',\n"; 
            
            $message .= "\n And lastly please add the follow Facade in the same file:\n";
            $message .= "'" . $Name . "'    => 'Services\\" . $Name . "\\" . $Name . "Facade',";
        }
        
        $message .= "\n\nAfter adding these your Service will be set up and accessible using " . $Name . ":: \n\nEnjoy!";
        
        // And returns message
        $this->info($message);
    }
}

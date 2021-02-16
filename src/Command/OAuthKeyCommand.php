<?php

namespace OAuthServer\Command;

use Hyperf\Utils\Arr;
use phpseclib3\Crypt\RSA;
use Hyperf\Command\Command;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class OAuthKeyCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oauth:key {--force : Overwrite keys they already exist}
                                      {--length=4096 : The length of the private key}';
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ConfigInterface
     */
    private $config;

    public function __construct(ContainerInterface $container, ConfigInterface $config)
    {
        parent::__construct();
        $this->container = $container;
        $this->config = $config;
    }

    public function handle()
    {
        $force = $this->input->getOption('force');
        $length = $this->input->getOption('length');

        [$publicKey, $privateKey] = [
            BASE_PATH . '/var/oauth-public.key',
            BASE_PATH . '/var/oauth-private.key',
        ];
        
        if ((file_exists($publicKey) || file_exists($privateKey)) && ! $force) {
            $this->error('Encryption keys already exist. Use the --force option to overwrite them.');
        } else {
            $private    = RSA::createKey($this->input ? (int) $length : 4096);
            $public     = $private->getPublicKey();
            
            file_put_contents($publicKey, $public);
            file_put_contents($privateKey, $private);

            $this->info('Encryption keys generated successfully.');
        }
    }

    protected function configure()
    {
        $this->setDescription('Create the encryption keys for API authentication');
    }
}

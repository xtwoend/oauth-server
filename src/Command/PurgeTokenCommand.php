<?php

namespace OAuthServer\Command;

use Carbon\Carbon;
use Hyperf\Command\Command;
use Hyperf\DbConnection\Db;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;


class PurgeTokenCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected ?string $signature = 'oauth:purge {--force : purge all expires token}';

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

    /**
     * Undocumented function
     *
     * @return void
     */
    public function handle()
    {
        $force = $this->input->getOption('force');

        $this->clear();

        $this->info('All token revoked');
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    protected function configure()
    {
        $this->setDescription('Purge all token expires & revoked');
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    private function clear()
    {
        $now = Carbon::now()->subHours(7)->format('Y-m-d H:i:s');

        $token = Db::connection(config('oauth.provider', 'default'))
            ->table('oauth_access_tokens')
            ->where('expires_at', '<=', $now)
            ->orWhere('revoked', 1)
            ->delete();

        $refresh = Db::connection(config('oauth.provider', 'default'))
            ->table('oauth_refresh_tokens')
            ->where('expires_at', '<=', $now)
            ->orWhere('revoked', 1)
            ->delete();
    }
}
<?php

namespace Point\Framework\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Point\Framework\Exceptions\DomainNotFoundException;
use stdClass;

class ClientMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (env('SERVER_DOMAIN') == 'app.point.red') {
            $pieces = explode('.', $request->getHost());

            if (count($pieces) < 4) {
                throw new DomainNotFoundException();
            }

            $project_url = $pieces[count($pieces) - 4];

            $config = array();
            $config['host'] = "localhost";
            $config['database'] = "p_".$project_url;

            \Config::set('database.connections.mysql.host', $config['host']);
            \Config::set('database.connections.mysql.database', $config['database']);

            $project = \DB::connection('client')->table('project')->where('url', '=', $project_url)->first();
            if (! $project) {
                throw new DomainNotFoundException();
            }

            $addons = \DB::connection('client')->table('addon_project')
                ->join('project', 'project.id', '=', 'addon_project.project_id')
                ->join('addon', 'addon.id', '=', 'addon_project.addon_id')
                ->select('addon.code')
                ->where('addon_project.project_id', '=', $project->id)
                ->get();
            if (! $addons) {
                throw new PointException('Addon not found');
            }

            \Config::set('point.client.name', $project->name);
            \Config::set('point.client.slug', $project->url);
            \Config::set('point.client.channel', $project->url);
            \Config::set('point.client.max_user', $project->number_of_user);
            \Config::set('point.client.max_storage', $project->number_of_storage);

            if (! $project->active) {
                throw new DomainNotFoundException();
            }
        } else {
            $project = new stdClass();
            $project->name = 'DEVELOPMENT';
            $project->url = 'dev';

            $addons = [
                ['code' => 'premium']
            ];

            \Config::set('point.client.name', $project->name);
            \Config::set('point.client.slug', $project->url);
            \Config::set('point.client.channel', $project->url);
            \Config::set('point.client.max_user', 99);
            \Config::set('point.client.max_storage', 0);
        }

        $user = auth()->user() ? : '';
        $database_name = \DB::connection()->getDatabaseName();

        $request->merge(compact('project', 'addons', 'user', 'database_name'));

        return $next($request);
    }
}

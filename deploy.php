<?php

namespace Deployer;

set('application', 'cms');
set('repository', 'git@github.com:C1C0/apis-cms.git');
set('branch', 'master');
set('nginx_web_root', '/var/www');
set('default_site', 'cms-test.c1c0.dk');
set('deploy_path', '{{nginx_web_root}}/{{default_site}}');
set('app_path', '{{deploy_path}}/current');
set('compose_file', 'compose.production.yaml');
set('docker_compose', 'docker compose');
set('proxy_pass', 'http://127.0.0.1:8080');
set('runtime_directories', [
    'content',
    'database',
    'users',
    'resources/users',
    'public/assets',
    'storage',
    'storage/app/private',
    'storage/app/public',
    'storage/framework/cache/data',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
    'docker-data/statamic-img-cache',
]);

host('cms-test.c1c0.dk')
    ->setHostname('46.224.57.217')
    ->setRemoteUser('deployer')
    ->setLabels([
        'stage' => 'production',
        'site' => 'cms-test.c1c0.dk',
    ]);

set('default_selector', 'stage=production');

desc('Create the base deployment directory');
task('deploy:setup', function () {
    run('mkdir -p {{deploy_path}}');
});

desc('Clone or update the application repository');
task('deploy:update_code', function () {
    $hasRepository = trim(run('if [ -d {{app_path}}/.git ]; then echo yes; else echo no; fi'));

    if ($hasRepository === 'yes') {
        run('cd {{app_path}} && git fetch --prune origin');
        run('cd {{app_path}} && git checkout {{branch}}');
        run('cd {{app_path}} && git pull --ff-only origin {{branch}}');

        return;
    }

    run('git clone --branch {{branch}} {{repository}} {{app_path}}');
});

desc('Prepare writable runtime paths required by bind mounts');
task('deploy:prepare_runtime_paths', function () {
    foreach (get('runtime_directories') as $directory) {
        run("mkdir -p {{app_path}}/{$directory}");
    }

    run('touch {{app_path}}/database/database.sqlite');
});

desc('Build the production Docker Compose image');
task('docker:build', function () {
    run('cd {{app_path}} && {{docker_compose}} -f {{compose_file}} build');
});

desc('Restart the production Docker Compose stack');
task('docker:restart', function () {
    run('cd {{app_path}} && {{docker_compose}} -f {{compose_file}} up -d --remove-orphans');
});

desc('Deploy the application');
task('deploy', [
    'deploy:setup',
    'deploy:update_code',
    'deploy:prepare_runtime_paths',
    'docker:build',
    'docker:restart',
]);

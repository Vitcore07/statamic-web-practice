<?php

namespace Deployer;

require 'recipe/common.php';

set('repository', 'git@github.com:C1C0/apis-cms.git');
set('branch', 'master');
set('deploy_path', '/var/www/cms-test.c1c0.dk');
set('compose_file', 'compose.production.yaml');
set('docker_compose', 'docker compose');
set('compose_project_name', 'cms');
set('keep_releases', 5);
set('dotenv', '{{deploy_path}}/shared/.env');
set('shared_files', ['.env']);
set('writable_dirs', []);
set('runtime_shared_directories', [
    'content',
    'database',
    'users',
    'resources/users',
    'public/assets',
    'storage',
    'docker-data/statamic-img-cache',
]);
set('runtime_seed_directories', [
    'content',
    'users',
    'resources/users',
]);
set('runtime_storage_directories', [
    'app/private',
    'app/public',
    'framework/cache/data',
    'framework/sessions',
    'framework/views',
    'logs',
]);

host('cms-test.c1c0.dk')
    ->setHostname('46.224.57.217')
    ->setRemoteUser('deployer')
    ->setLabels(['stage' => 'production']);

function composeEnv(): array
{
    return [
        'APP_SHARED_PATH' => get('deploy_path').'/shared',
        'APP_ENV_FILE' => get('deploy_path').'/shared/.env',
        'COMPOSE_PROJECT_NAME' => get('compose_project_name'),
    ];
}

desc('Prepare shared runtime paths required by Docker bind mounts');
task('deploy:runtime_paths', function () {
    foreach (get('runtime_shared_directories') as $directory) {
        run("mkdir -p {{deploy_path}}/shared/{$directory}");
    }

    foreach (get('runtime_seed_directories') as $directory) {
        run("if [ -d {{release_path}}/{$directory} ]; then cp -an {{release_path}}/{$directory}/. {{deploy_path}}/shared/{$directory}/ 2>/dev/null || true; fi");
    }

    foreach (get('runtime_storage_directories') as $directory) {
        run("mkdir -p {{deploy_path}}/shared/storage/{$directory}");
    }

    run('touch {{deploy_path}}/shared/database/database.sqlite');
});

desc('Build the Docker image for the new release');
task('docker:build_release', function () {
    run('cd {{release_path}} && {{docker_compose}} -f {{compose_file}} build', env: composeEnv());
});

desc('Build the Docker image for the current release');
task('docker:build_current', function () {
    run('cd {{current_path}} && {{docker_compose}} -f {{compose_file}} build', env: composeEnv());
});

desc('Restart the Docker Compose stack');
task('docker:restart', function () {
    run('cd {{current_path}} && {{docker_compose}} -f {{compose_file}} up -d --remove-orphans', env: composeEnv());
});

desc('Deploy the application');
task('deploy', [
    'deploy:prepare',
    'deploy:runtime_paths',
    'docker:build_release',
    'deploy:publish',
]);

after('deploy:symlink', 'docker:restart');
after('rollback', 'docker:build_current');
after('docker:build_current', 'docker:restart');
after('deploy:failed', 'deploy:unlock');

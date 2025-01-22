<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AdminControllerTest extends WebTestCase
{
    public function dashboard_displays_admin_dashboard()
    {
        $client = static::createClient();
        $client->request('GET', '/admin');

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertSelectorExists('div.dashboard');
    }

    public function dashboard_redirects_if_not_admin()
    {
        $client = static::createClient();
        $client->request('GET', '/admin');

        $this->assertResponseRedirects('/home');
    }

    public function startWorker_starts_worker_successfully()
    {
        $client = static::createClient();
        $client->request('POST', '/admin/worker/start');

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['status' => 'success', 'message' => 'Worker started successfully.']),
            $client->getResponse()->getContent()
        );
    }

    public function startWorker_returns_error_on_failure()
    {
        $client = static::createClient();
        $client->request('POST', '/admin/worker/start');

        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $client->getResponse()->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['status' => 'error', 'message' => 'Failed to start the worker: ...']),
            $client->getResponse()->getContent()
        );
    }

    public function stopWorker_stops_worker_successfully()
    {
        $client = static::createClient();
        $client->request('POST', '/admin/worker/stop');

        $this->assertResponseRedirects('/admin/application');
    }

    public function workerStatus_returns_worker_status()
    {
        $client = static::createClient();
        $client->request('GET', '/admin/worker/status');

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['status' => 'success', 'workerRunning' => true]),
            $client->getResponse()->getContent()
        );
    }

    public function users_displays_user_list()
    {
        $client = static::createClient();
        $client->request('GET', '/admin/users');

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertSelectorExists('table.users');
    }

    public function users_redirects_if_not_admin()
    {
        $client = static::createClient();
        $client->request('GET', '/admin/users');

        $this->assertResponseRedirects('/home');
    }

    public function deleteUser_deletes_user_successfully()
    {
        $client = static::createClient();
        $client->request('POST', '/admin/users/delete/1', ['_token' => 'valid_token']);

        $this->assertResponseRedirects('/admin/users');
    }

    public function deleteUser_returns_error_on_failure()
    {
        $client = static::createClient();
        $client->request('POST', '/admin/users/delete/1', ['_token' => 'invalid_token']);

        $this->assertResponseRedirects('/admin/users');
    }

    public function desactivateUser_desactivates_user_successfully()
    {
        $client = static::createClient();
        $client->request('POST', '/admin/users/desactivate/1');

        $this->assertResponseRedirects('/app/profile/view/1');
    }

    public function activateUser_activates_user_successfully()
    {
        $client = static::createClient();
        $client->request('POST', '/admin/users/activate/1');

        $this->assertResponseRedirects('/app/profile/view/1');
    }

    public function importUsers_displays_import_form()
    {
        $client = static::createClient();
        $client->request('GET', '/admin/import-users');

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertSelectorExists('form#import-users');
    }

    public function confirmImport_imports_users_successfully()
    {
        $client = static::createClient();
        $client->request('POST', '/admin/confirm-import', ['send_emails' => 'on']);

        $this->assertResponseRedirects('/admin/import-users');
    }

    public function events_displays_event_list()
    {
        $client = static::createClient();
        $client->request('GET', '/admin/events');

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertSelectorExists('table.events');
    }

    public function events_redirects_if_not_admin()
    {
        $client = static::createClient();
        $client->request('GET', '/admin/events');

        $this->assertResponseRedirects('/home');
    }
}
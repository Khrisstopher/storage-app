<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Session;
use App\Services\GroupService;

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/../../services/GroupService.php';


/**
 * Controlador para la gestión de grupos.
 * @author Khrisstopher
 * @link https://www.linkedin.com/in/khrisstopher/
 */
class GroupController extends Controller {
    private GroupService $groupService;

    public function __construct(\PDO $pdo) {
        $this->groupService = new GroupService($pdo);
    }

    public function list() {
        try {
            $this->requireAdmin();
            $groups = $this->groupService->getGroups();
            $this->response(true, 'Grupos obtenidos correctamente', $groups);
        } catch (\Throwable $e) {
            $this->logError($e, "GET_GROUPS");
        }
    }

    public function create() {
        try {
            $this->requireAdmin();
            $data = $this->getRequestData();
            $result = $this->groupService->createGroup($data);
            $this->response(true, 'Grupo creado correctamente', $result);
        } catch (\Throwable $e) {
            $this->logError($e, "CREATE_GROUP");
        }
    }

    public function update() {
        try {
            $this->requireAdmin();
            $data = $this->getRequestData();
            $result = $this->groupService->updateGroup($data);
            $this->response(true, 'Grupo actualizado correctamente', $result);
        } catch (\Throwable $e) {
            $this->logError($e, "UPDATE_GROUP");
        }
    }

    public function delete() {
        try {
            $this->requireAdmin();
            $data = $this->getRequestData();
            $result = $this->groupService->deleteGroup($data['id']);
            $this->response(true, 'Grupo eliminado correctamente', $result);
        } catch (\Throwable $e) {
            $this->logError($e, "DELETE_GROUP");
        }
    }
}
<?php
 namespace OCA\Files_external_onedrive\Controller;

 use Exception;

 use OCP\IRequest;
 use OCP\AppFramework\Http;
 use OCP\AppFramework\Http\DataResponse;
 use OCP\AppFramework\Controller;

 use OCA\Files_external_onedrive\Db\ExternalConfig;
 use OCA\Files_external_onedrive\Db\ExternalConfigMapper;

 class ExternalConfigController extends Controller {

     private $mapper;
     private $mountId;

     public function __construct($AppName, IRequest $request, NoteMapper $mapper, $mountId){
         parent::__construct($AppName, $request);
         $this->mapper = $mapper;
         $this->mountId = $mountId;
     }

     /**
      * @NoAdminRequired
      */
     public function index() {
         return new DataResponse($this->mapper->findAll($this->mountId));
     }

     /**
      * @NoAdminRequired
      *
      * @param int $id
      */
     public function show($id) {
         try {
             return new DataResponse($this->mapper->find($id));
         } catch(Exception $e) {
             return new DataResponse([], Http::STATUS_NOT_FOUND);
         }
     }

     /**
      * @NoAdminRequired
      *
      * @param string $title
      * @param string $content
      */
     /*public function create($title, $content) {
         $note = new Note();
         $note->setTitle($title);
         $note->setContent($content);
         $note->setUserId($this->userId);
         return new DataResponse($this->mapper->insert($note));
     }*/

     /**
      * @NoAdminRequired
      *
      * @param int $id
      * @param string $title
      * @param string $content
      */
     public function update($id, $key, $value) {
         try {
             $externalConfig = $this->mapper->find($id);
         } catch(Exception $e) {
             return new DataResponse([], Http::STATUS_NOT_FOUND);
         }
         $externalConfig->setKey($key);
         $externalConfig->setValue($value);
         return new DataResponse($this->mapper->update($externalConfig));
     }

     /**
      * @NoAdminRequired
      *
      * @param int $id
      */
     /*public function destroy($id) {
         try {
             $note = $this->mapper->find($id, $this->userId);
         } catch(Exception $e) {
             return new DataResponse([], Http::STATUS_NOT_FOUND);
         }
         $this->mapper->delete($note);
         return new DataResponse($note);
     }*/

 }
<?php
namespace OCA\MiageExemple\Controller;

use DateTime;
use OC\AllConfig;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\IRequest;
use OCP\Activity\IEvent;
use OCP\Activity\IExtension;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use OCP\IURLGenerator;
use OCP\PreConditionNotMetException;


class PageController extends Controller {
	private $userId;

	/** @var IDBConnection */
	protected $connection;

	/** @var AllConfig */
	private $AllConfig;

	/** @var IURLGenerator */
	private $urlGenerator;

	protected $array = [

	// USER //

	'user_created' => 'User created',
	'user_deleted' => 'User deleted',

	// FILE/FOLDER //
	'created_self' => 'File/folder created',
	'created_by' => 'File/folder created',
	'changed_self' => 'File/folder edited',
	'changed_by' => 'File/folder edited',
	'renamed_self' => 'File/folder renamed',
	'renamed_by' => 'File/folder renamed',
	'deleted_self' => 'File/folder deleted',
	'deleted_by' => 'File/folder deleted',
	'moved_self' => 'File/folder moved',
	'restored_self' => 'File/folder restored',

	// COMMENT //
	'add_comment_subject' => 'New comment created',

	// EVENT //
	'object_add_event' => 'Event created',
	'object_add_event_self' => 'Event created',
	'object_delete_event' => 'Event deleted',
	'object_delete_event_self' => 'Event deleted',
	'object_update_event' => 'Event updated',
	'object_update_event_self' => 'Event updated',

	// CALENDAR //
	'calendar_add_self' => 'Calendar created',
	'calendar_add' => 'Calendar created',
	'calendar_delete_self' => 'Calendar deleted',
	'calendar_delete' => 'Calendar deleted',
	'calendar_update_self' => 'Calendar updated',
	'calendar_update' => 'Calendar updated',

	// CALENDAR SHARED //

	'calendar_publish_self' => 'Calendar shared link created',
	'calendar_unpublish_self' => 'Calendar shared link removed',
	'calendar_user_share' => 'Calendar shared',
	'calendar_user_share_you' => 'Calendar shared',
	'calendar_group_share_you' => 'Calendar shared',
	'calendar_update' => 'Calendar edited',
	'calendar_update_self' => 'Calendar edited',
	'calendar_user_unshare' => 'Calendar unshared',
	'calendar_user_unshare_you' => 'Calendar shared',
	'calendar_group_unshare_you' => 'Calendar unshared',

	// SHARED //
	'shared_with_by' => 'File/folder shared',
	'shared_user_self' => 'File/Folder shared',
	'shared_group_self' => 'File/Folder shared',
	'unshared_user_self' => 'File/Folder unshared',
	'unshared_group_self' => 'File/Folder unshared',
	'unshared_by' => 'File/Folder unshared',

	// SHARED LINK //
	'shared_link_self' => 'Shared link created',
	'unshared_link_self' => 'Shared link removed',

	// FAVORITE //
	'added_favorite' => 'Added to favorite',
	'removed_favorite' => 'Removed from favorite',

	// TAG //
	'create_tag' => 'New tag created',
	'delete_tag' => 'Tag deleted',
	'assign_tag' => 'Tag assigned',
	'unassign_tag' => 'Tag removed',

	];

	public function __construct($AppName, IRequest $request, $UserId,IDBConnection $connection, AllConfig $allConfig, IURLGenerator $urlGenerator){
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
		$this->connection = $connection;
		$this->AllConfig = $allConfig;
		$this->urlGenerator = $urlGenerator;
	}

		/**
	 * CAUTION: the @Stuff turns off security checks; for this page no admin is
	 *          required and no CSRF check. If you don't know what CSRF is, read
	 *          it up in the docs or you might create a security hole. This is
	 *          basically the only required method to add this exemption, don't
	 *          add it to any other method if you don't exactly know what it does
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index() {

	    $debut = $this->AllConfig->getUserValue($this->userId, 'miage-nextcloud-traces', 'date_debut');
	    $fin   = $this->AllConfig->getUserValue($this->userId, 'miage-nextcloud-traces', 'date_fin');

	    if ($debut == NULL OR $fin == NULL) {
	        $params['premiere_fois'] = true;
            return new RedirectResponse($this->urlGenerator->linkToRoute('miage-nextcloud-traces.page.param', $params));
        }


		$query = $this->connection->getQueryBuilder();
		$query->select('*')
		->from('activity')
        ->where('timestamp > :debut')
        ->andWhere('timestamp < :fin')
        ->setParameter('debut', strtotime($debut))
        ->setParameter('fin',   strtotime($fin));

		$result = $query->execute();
		$response = [] ;
		while ($row = $result->fetch()) {
			$headers['X-Activity-Last-Given'] = (int) $row['activity_id'];
			$response[] = $row;
		}
		$result->closeCursor();
		$response = $this->formateData($response);
		$parameters = array('response' => $response);

		$parameters['param_url'] = $this->urlGenerator->linkToRoute('miage-nextcloud-traces.page.param');
		$parameters['index_url'] = $this->urlGenerator->linkToRoute('miage-nextcloud-traces.page.index');

		return new TemplateResponse('miage-nextcloud-traces', 'index', $parameters);  // templates/index.php
	}

    /**
     * CAUTION: the @Stuff turns off security checks; for this page no admin is
     *          required and no CSRF check. If you don't know what CSRF is, read
     *          it up in the docs or you might create a security hole. This is
     *          basically the only required method to add this exemption, don't
     *          add it to any other method if you don't exactly know what it does
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     */


    public function param($debut, $fin) {

        $params = array();

        $params['param_url'] = $this->urlGenerator->linkToRoute('miage-nextcloud-traces.page.param');
        $params['index_url'] = $this->urlGenerator->linkToRoute('miage-nextcloud-traces.page.index');

        if (isset($debut) AND isset($fin)) {
            if (($this->validateDate($debut)) AND ($this->validateDate($fin))) {
                try {
                    $this->AllConfig->setUserValue($this->userId, 'miage-nextcloud-traces', 'date_debut', $debut);
                    $this->AllConfig->setUserValue($this->userId, 'miage-nextcloud-traces', 'date_fin',   $fin);
                } catch (PreConditionNotMetException $e) {
                    $params['error'] = true;
                }
                $params['error'] = false;
            } else {
                $params['error'] = true;
            }
        }

        $params['debut'] = $this->AllConfig->getUserValue($this->userId, 'miage-nextcloud-traces', 'date_debut');
        $params['fin']   = $this->AllConfig->getUserValue($this->userId, 'miage-nextcloud-traces', 'date_fin');

        return new TemplateResponse('miage-nextcloud-traces', 'param', $params);
    }

    public function paramInsert($param){
        return new TemplateResponse('miage-nextcloud-traces', 'param');
    }
	/**
	 * CAUTION: the @Stuff turns off security checks; for this page no admin is
	 *          required and no CSRF check. If you don't know what CSRF is, read
	 *          it up in the docs or you might create a security hole. This is
	 *          basically the only required method to add this exemption, don't
	 *          add it to any other method if you don't exactly know what it does
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function get($filter = 'all') {

		$query = $this->connection->getQueryBuilder();
		if($filter == 'all'){
			$query->select('*')
			->from('activity');

			$result = $query->execute();
			$response = [] ;
			while ($row = $result->fetch()) {
				$headers['X-Activity-Last-Given'] = (int) $row['activity_id'];
				$response[] = $row;
			}
			$result->closeCursor();
			$response = $this->formateData($response);
			$parameters = array('response' => $response);
		}else{
			$query->select('*')
			->from('activity')
			->where($query->expr()->like('type', $query->createNamedParameter("%".$filter."%")));

			$result = $query->execute();
			$response = [] ;
			while ($row = $result->fetch()) {
				$headers['X-Activity-Last-Given'] = (int) $row['activity_id'];
				$response[] = $row;
			}
			$result->closeCursor();
			$response = $this->formateData($response);
			$parameters = array('response' => $response);
		}
		

		return new DataResponse($parameters);
	}

	public function getFilter($filter){
		return $this->get($filter);
	}


	public function formateData($data){

		for($i=0;$i<sizeof($data);$i++){
			// DATE COLUMN
			$data[$i]['timestamp'] = date("d/m/Y H:i", $data[$i]['timestamp']);
			//FILE COLUMN
			if($data[$i]['file'] == ''){
				if(strpos($data[$i]['subjectparams'],'filePath')){
					$start_position = strrpos($data[$i]['subjectparams'],'filePath') + 11;
					$end_position = (strlen($data[$i]['subjectparams'])-2) - $start_position;
					$data[$i]['file'] = substr($data[$i]['subjectparams'],$start_position,$end_position);
				}else if($data[$i]['object_type'] == 'calendar'){
					$start_position = strrpos($data[$i]['subjectparams'],'name') + 7;
					$end_position = (strpos($data[$i]['subjectparams'], '"', $start_position)) - $start_position;
					$data[$i]['file'] = substr($data[$i]['subjectparams'],$start_position,$end_position);
				}else if($data[$i]['subject'] == 'create_tag'){
					$start_position = strrpos($data[$i]['subjectparams'],'name') + 9;
					$end_position = (strrpos($data[$i]['subjectparams'], '\"', $start_position)) - $start_position;
					$data[$i]['file'] = substr($data[$i]['subjectparams'],$start_position,$end_position);
				}else if($data[$i]['subject'] == 'assign_tag' || $data[$i]['subject'] == 'unassign_tag'){
					$start_position = strrpos($data[$i]['subjectparams'],'name') + 9;
					$end_position = (strpos($data[$i]['subjectparams'], '\"', $start_position)) - $start_position;
					$data[$i]['file'] = substr($data[$i]['subjectparams'],$start_position,$end_position);
				}else if($data[$i]['app'] == 'user'){
					$data[$i]['file'] = 'N/A';
				}else{
					$data[$i]['file'] = $data[$i]['subject'];
				}
			}else{
				str_replace("\/","/",$data[$i]['file']);
			} 

			//AFFECTED TO COLUMN
			if($data[$i]['subject']=='shared_group_self' || $data[$i]['subject']=='unshared_group_self'){
				$start_position = strrpos($data[$i]['subjectparams'],'"},"') + 4;
				$end_position = (strpos($data[$i]['subjectparams'], '"', $start_position)) - $start_position;
				$data[$i]['affecteduser'] = substr($data[$i]['subjectparams'],$start_position,$end_position) . ' (group)';
			}else if($data[$i]['subject']=='calendar_group_share_you'){
				$start_position = strrpos($data[$i]['subjectparams'],'"group":"') + 9;
				$end_position = (strpos($data[$i]['subjectparams'], '"', $start_position)) - $start_position;
				$data[$i]['affecteduser'] = substr($data[$i]['subjectparams'],$start_position,$end_position) . ' (group)';
			}

			// ACTION COLUMN
			if($data[$i]['subject'] == 'assign_tag' || $data[$i]['subject'] == 'unassign_tag'){
				$start_position = strpos($data[$i]['subjectparams'],'","') + 3;
				$end_position = (strpos($data[$i]['subjectparams'], '","', $start_position)) - $start_position;
				$assigned_target = str_replace("\/","/",substr($data[$i]['subjectparams'],$start_position,$end_position));
				$data[$i]['subject'] = $this->array[$data[$i]['subject']] . ' to ' . $assigned_target;
			}else if($data[$i]['type'] == 'calendar_event'){
				$start_position = strpos($data[$i]['subjectparams'],'name') + 7;
				$end_position = (strpos($data[$i]['subjectparams'], '"', $start_position)) - $start_position;
				$event_target = substr($data[$i]['subjectparams'],$start_position,$end_position);
				$data[$i]['subject'] = $this->array[$data[$i]['subject']] . ' to calendar ' . $event_target;
			}else if($data[$i]['subject'] == 'user_edited'){
				$start_position = strpos($data[$i]['subjectparams'],'p"') + 4;
				$end_position = (strpos($data[$i]['subjectparams'], '"', $start_position)) - $start_position;
				$event_target = substr($data[$i]['subjectparams'],$start_position,$end_position);
				if(strpos($data[$i]['subjectparams'],'remove')){
					$data[$i]['subject'] = 'User removed from group ' . $event_target;
				}else{
					$data[$i]['subject'] = 'User added to group ' . $event_target;
				}
				

				
			}else{
				$data[$i]['subject'] = $this->array[$data[$i]['subject']];
			}

		}
		return $data;
	}

	/* Validation date format */
    private function validateDate($date, $format = 'Y-m-d'){
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

}

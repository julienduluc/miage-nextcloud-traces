<?php

namespace OCA\MiageExemple;

use OCA\Activity\Data;
use OCA\Activity\Extension\Files_Sharing;
use OCP\Activity\IManager;
use OC\User;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use OC;
use OCP\Files\NotFoundException;
use OCP\IDBConnection;
use OCP\IURLGenerator;
use OC\Files;


class ActivityNotification
{

    /** @var \OCP\Activity\IManager */
    private $manager;
    /** @var \OCA\Activity\Data */
    private $activityData;
    /** @var IRootFolder */
    private $rootFolder;
    /** @var IURLGenerator */
    private $urlGenerator;
    /** @var \OCP\Files\Folder */
    private $baseFolder;
    /** @var IDBConnection */
    private $db;


    public function __construct(IManager $manager,
                                Data $activityData,
                                IRootFolder $rootFolder,
                                IURLGenerator $urlGenerator,
                                IDBConnection $db
                                ) {
        $this->manager      = $manager;
        $this->activityData = $activityData;
        $this->rootFolder   = $rootFolder;
        $this->urlGenerator = $urlGenerator;
        $this->db           = $db;
    }
    /**
     * Adds the suspect activity to the database
     *
     * @param string $user
     * @param int $fileId
     */
    public function registerSuspectActivity($user, $fileId) {


        $path = $this->getFile($fileId)->getPath();
        $time = time();

        $event = $this->manager->generateEvent();
        $event->setApp('files')
            ->setType(Files_Sharing::TYPE_SHARED)
            ->setAuthor($user)
            ->setAffectedUser($this->getFile($fileId)->getOwner()->getDisplayName())
            ->setTimestamp($time)
            ->setSubject("denied_access", array($fileId, $path))
            ->setObject('files', $fileId, $path)
            ->setLink($this->getLink($fileId));

        $this->activityData->send($event);
        $this->sendEmail($user, $path, $fileId, $time);
    }

    /* Difference between 403 and 404 */
    public function ifDeniedAcess($id) {

        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
            ->from('filecache')
            ->where(
                $qb->expr()->eq('fileid', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT))
            );

        $cursor = $qb->execute();
        $row = $cursor->fetch();
        $cursor->closeCursor();

        return $row;

    }

    /* Get list of all users */
    private function getUsers() {
        $db = new User\Database();
        return $db->getUsers();
    }

    /* Get file instance from id */
    private function getFile($id) {

        $users = $this->getUsers();

        foreach ($users as $user) {
            $baseFolder = $this->rootFolder->getUserFolder($user);
            $files      = $baseFolder->getById($id);
            if ($files) {
                $this->baseFolder = $baseFolder;
                return current($files);
            }
        }

        return null;
    }

    /* Get the absolute link of the file */
    private function getLink($id) {
        $file = $this->getFile($id);

        $baseFolder = $this->baseFolder;

        if ($file instanceof Folder) {
            // set the full path to enter the folder
            $params['dir'] = $baseFolder->getRelativePath($file->getPath());
        } else {
            // set parent path as dir
            $params['dir'] = $baseFolder->getRelativePath($file->getParent()->getPath());
            // and scroll to the entry
            $params['scrollto'] = $file->getName();
        }

        return $this->urlGenerator->linkToRouteAbsolute('files.view.index', $params);
    }

    /* Send the notification with critical informations */
    private function sendEmail($user, $path, $fileId, $time) {
        $text = '<ul>';
        $text .= '<li>Utilisateur frauduleux : <b>'.$user.'</b></li>';
        $text .= '<li>Tentative d\'accès à : <b>'.$path .'</b></li>';
        $text .= '<li>Via le lien : <b>'.$this->getLink($fileId).'</b></li>';
        $text .= '<li>Propriétaire de l\'espace de stockage : <b>'.$this->getFile($fileId)->getOwner()->getDisplayName().'</b></li>';
        $text .= '</ul>';

        $mailer = \OC::$server->getMailer();
        $template = $mailer->createEMailTemplate('settings.TestEmail', [
            'displayname' => 'Nextcloud',
        ]);

        $template->setSubject('Nexcloud - Tentative d\'accès frauduleux');
        $template->addHeader();
        $template->addHeading('Tentative de fraude le : '.date('d/m/Y H:i:s', $time));
        $template->addBodyText($text, 'text/html');
        $template->addFooter();

        $message = $mailer->createMessage();
        $message->setTo(['brahimelo.pro@gmail.com' => 'GROSTEST']);
        $message->useTemplate($template);
        $mailer->send($message);
    }
}
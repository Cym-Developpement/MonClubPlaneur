<?php
namespace App\library;

use App\Models\alert;
/**
 * 
 */
class alerts
{
	
	function __construct()
	{
		# code...
	}


	static public function getAlertsList($idUser)
	{
		$alertListData = alert::where('userId', $idUser)->where('read', 0)->get();
		$alertListHTML = "";
		foreach ($alertListData as $key => $value) {
			$text = str_replace('[ID]', $value->id, $value->text);
			$alertListHTML .= '<div class="alert alert-warning alert-dismissible fade show d-none d-lg-block" role="alert">'.$text.'
			<button type="button" class="close" data-dismiss="alert" aria-label="Close" ';
			if ($value->markAsReadOnClose == 1) {
				$alertListHTML .= 'onclick="markReadAlert('.$value->id.');"';
			}
			$alertListHTML .= '>
              <span aria-hidden="true">&times;</span>
            </button>
			</div>';
		}

		return $alertListHTML;
	}

	static public function markAsRead($idUser, $idAlert)
	{
		$alert = alert::where('id', $idAlert)->where('userId', $idUser)->first();
		if ($alert->id == $idAlert) {
			$alert->read = 1;
			$alert->save();
		}
	}
}
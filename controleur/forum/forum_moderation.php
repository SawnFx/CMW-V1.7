<?php 
if(isset($_GET['id_topic']) AND isset($_GET['choix']))
{
	$id = htmlspecialchars($_GET['id_topic']);
	$choix = htmlspecialchars($_GET['choix']);
	if($choix == 1 && ($_PGrades_['PermsForum']['moderation']['closeTopic'] == true OR $_Joueur_['rang'] ==1))
	{
		//On lock
		$close = $bddConnection->prepare('UPDATE cmw_forum_post SET etat = 1 WHERE id = :id');
		$close->execute(array(
			'id' => $id
		));
		header('Location: ?&page=forum');
	}
	elseif($choix == 2 && ($_PGrades_['PermsForum']['moderation']['deleteTopic'] == true OR $_Joueur_['rang'] == 1))
	{
		//on supprime le topic 
		if(!isset($_GET['confirmation']))
		{
			header('Location: ?&page=confirmation&id_topic=' .$id. '&choix=2');
		}
		else
		{
			if(isset($_POST['reason']))
			{
				$reason = htmlspecialchars($_POST['reason']);
				$info_remove = $bddConnection->prepare('SELECT * FROM cmw_forum_post WHERE id = :id');
				$info_remove->execute(array(
					'id' => $id
				));
				$info_removed = $info_remove->fetch(PDO::FETCH_ASSOC);
				$count_answer = $bddConnection->prepare('SELECT COUNT(id) AS id FROM cmw_forum_answer WHERE id_topic = :id');
				$count_answer->execute(array(
					'id' => $id
				));
				$count_answerd = $count_answer->fetch(PDO::FETCH_ASSOC);
				$removed = $bddConnection->prepare('INSERT INTO cmw_forum_topic_removed (nom, nb_reponse, 
				auteur_topic, date_creation, raison, date_suppression, auteur_suppression) VALUES (:nom,
				:nb_reponse, :auteur_topic, :date_creation, :raison, NOW(), :auteur_suppression)');
				$removed->execute(array(
					'nom' => $info_removed['nom'],
					'nb_reponse' => $count_answerd['id'],
					'auteur_topic' => $info_removed['pseudo'],
					'date_creation' => $info_removed['date_creation'],
					'raison' => $reason,
					'auteur_suppression' => $_Joueur_['pseudo']
				));
				$delete_answer = $bddConnection->prepare('DELETE FROM cmw_forum_answer WHERE id_topic = :id');
				$delete_answer->execute(array(
					'id' =>$id 
				));
				$delete_post = $bddConnection->prepare('DELETE FROM cmw_forum_post WHERE id = :id');
				$delete_post->execute(array(
					'id' => $id 
				));
				header('Location: ?&page=forum');
			}
			else
				header('Location: ?page=erreur&erreur=0');
		}
	}
	elseif($choix == 3 && ($_PGrades_['PermsForum']['moderation']['mooveTopic'] == true OR $_Joueur_['rang'] == 1))
	{
		//Alors on déplace :P
		if(!isset($_GET['confirmation']))
		{
			header('Location: ?&page=confirmation&id_topic=' . $id . '&choix=3');
			
		}
		else 
		{
			if(isset($_POST['emplacement']))
			{
				$emplacement = htmlspecialchars($_POST['emplacement']);
				$emplacementd = explode('_', $emplacement);
				if(!empty($emplacementd[1]))
				{
					$edit = $bddConnection->prepare('UPDATE cmw_forum_post SET id_categorie = :id_categorie , sous_forum = :sous_forum WHERE id = :id');
					$edit->execute(array(
						'id_categorie' => $emplacementd[0],
						'sous_forum' => $emplacementd[1],
						'id' => $id
					));
				}
				else 
				{
					$edit = $bddConnection->prepare('UPDATE cmw_forum_post SET id_categorie = :id_categorie, sous_forum = NULL WHERE id = :id');
					$edit->execute(array(
						'id_categorie' => $emplacementd[0],
						'id' => $id
					));
				}
				header('Location: ?&page=forum');
			}
			else
				header('Location: ?page=erreur&erreur=0');
		}
	}
	elseif($choix == 4 && ($_PGrades_['PermsForum']['moderation']['closeTopic'] == true OR $_Joueur_['rang'] == 1))
	{
		//On rouvre 
		$ouvre = $bddConnection->prepare('UPDATE cmw_forum_post SET etat = 0 WHERE id = :id');
		$ouvre->execute(array(
			'id' => $id
		));
		header('Location: ' . $_Serveur_['General']['url'] . '?&page=forum');
	}
	else
		header('Location: ?page=erreur&erreur=');
}
else
	header('Location: ?page=erreur&erreur=0');
?>
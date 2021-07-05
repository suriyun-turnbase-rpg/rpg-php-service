<?php
function ReadMail($id) {
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    $mailDb = new Mail();
    $mail = $mailDb->find(array(
        'playerId = ? AND isDelete = 0 AND id = ?',
        $playerId,
        $id
    ));
    if ($mail) {
        $mail->isRead = 1;
        $mail->readTimestamp = date('Y-m-d H:i:s', time());
        $mail->save();
        $output['mail'] = CursorToArray($mail, array('readTimestamp','claimTimestamp','deleteTimestamp','sentTimestamp'));
    }
    echo json_encode($output);
}

function ClaimMailRewards($id) {
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    $createItems = array();
    $updateItems = array();
    $updateCurrencies = array();
    $mailDb = new Mail();
    $mail = $mailDb->findone(array(
        'playerId = ? AND isDelete = 0 AND hasReward = 1 AND isClaim = 0 AND id = ?',
        $playerId,
        $id
    ));
    if ($mail) {
        $mail->isClaim = 1;
        $mail->claimTimestamp = date('Y-m-d H:i:s', time());
        $mail->save();
        // Add items to inventory
        $addItemsDone = true;
        $items = json_decode($mail->items, true);
        foreach ($items as $key => $value) {
            $id = $value['id'];
            $amount = $value['amount'];
            $addItemsResult = AddItems($playerId, $id, $amount);
            if ($addItemsResult['success'])
            {
                $resultCreateItems = $addItemsResult['createItems'];
                $resultUpdateItems = $addItemsResult['updateItems'];
                $countCreateItems = count($resultCreateItems);
                $countUpdateItems = count($resultUpdateItems);
                for ($j = 0; $j < $countCreateItems; ++$j)
                {
                    $createItems[] = $resultCreateItems[$j];
                }
                for ($j = 0; $j < $countUpdateItems; ++$j)
                {
                    $updateItems[] = $resultUpdateItems[$j];
                }
            }
            else
            {
                // Error occurs when add items
                $addItemsDone = false;
                break;
            }
        }
        if ($addItemsDone) {
            // Applies add items
            foreach ($createItems as $key => $createItem) {
                $createItem->save();
                HelperUnlockItem($playerId, $createItem->dataId);
            }
            foreach ($updateItems as $key => $updateItem) {
                $updateItem->update();
            }
            // Add currencies
            $currencies = json_decode($mail->currencies, true);
            foreach ($currencies as $key => $value) {
                $id = $value['id'];
                $amount = $value['amount'];
                $updateCurrency = GetCurrency($playerId, $id);
                $updateCurrency->amount += $amount;
                $updateCurrency->update();
                $updateCurrencies[] = $updateCurrency;
            }
        } else {
            $output['error'] = 'ERROR_CANNOT_RECEIVE_ALL_ITEMS';
        }
    }
    $output['createItems'] = ItemCursorsToArray($createItems);
    $output['updateItems'] = ItemCursorsToArray($updateItems);
    $output['updateCurrencies'] = CursorsToArray($updateCurrencies);
    echo json_encode($output);
}

function DeleteMail($id) {
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    $mailDb = new Mail();
    $mail = $mailDb->findone(array(
        'playerId = ? AND isDelete = 0 AND (hasReward = 0 OR isClaim = 1) AND id = ?',
        $playerId,
        $id
    ));
    if ($mail) {
        $mail->isDelete = 1;
        $mail->deleteTimestamp = date('Y-m-d H:i:s', time());
        $mail->save();
    }
    echo json_encode($output);
}

function GetMailsCount() {
    $player = GetPlayer();
    $playerId = $player->id;
    $mailDb = new Mail();
    $count = $mailDb->count(array(
        'playerId = ? AND isDelete = 0 AND (isRead = 0 OR (hasReward = 1 AND isClaim = 0)',
        $playerId
    ));
    echo json_encode(array('count' => $count));
}
?>
<?php
function ReadMail($id) {
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    $mail = $mailDb->findone(array(
        'playerId = ? AND isDelete = 0',
        $playerId
    ));
    if ($mail) {
        $mail->isRead = 1;
        $mail->readTimestamp = 'NOW()';
        $mail->save();
    }
    echo json_encode($output);
}

function ClaimMailRewards($id) {
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    $mail = $mailDb->findone(array(
        'playerId = ? AND isDelete = 0 AND hasReward = 1 AND isClaim = 0',
        $playerId
    ));
    $updateItems = array();
    $updateCurrencies = array();
    if ($mail) {
        $mail->isClaim = 1;
        $mail->claimTimestamp = 'NOW()';
        $mail->save();
        // Add items to inventory
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
                    $createItem = $resultCreateItems[$j];
                    $createItem->save();
                    HelperUnlockItem($playerId, $createItem->dataId);
                    $createItems[] = $createItem;
                }
                for ($j = 0; $j < $countUpdateItems; ++$j)
                {
                    $updateItem = $resultUpdateItems[$j];
                    $updateItem->update();
                    $updateItems[] = $updateItem;
                }
            }
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
    }
    $output['createItems'] = array();
    $output['updateItems'] = array();
    $output['updateCurrencies'] = CursorsToArray($updateCurrencies);
    echo json_encode($output);
}

function DeleteMail($id) {
    $output = array('error' => '');
    $player = GetPlayer();
    $playerId = $player->id;
    $mail = $mailDb->findone(array(
        'playerId = ? AND isDelete = 0 AND (hasReward = 0 OR isClaim = 1)',
        $playerId
    ));
    if ($mail) {
        $mail->isDelete = 1;
        $mail->deleteTimestamp = 'NOW()';
        $mail->save();
    }
    echo json_encode($output);
}
?>
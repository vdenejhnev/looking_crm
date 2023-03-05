<?php

namespace Model;

use App\Model;

class Notification extends Model {
    public static function create($dealer, $lead, $recurring_lead, $fields) {
        global $CONNECTION;

        $stmt = $CONNECTION->prepare("INSERT INTO notification (dealer, lead, recurring_lead, type, fields) VALUES ('$dealer', '$lead', '$recurring_lead', 'coincidence', '$fields')");
        $stmt->execute();

        $dealer_data = Dealer::findOne(['user_id' => $dealer]); 

        if ($dealer_data->email_notification == 1 ){
            $recurring_lead_data = Lead::findOne(['id' => $recurring_lead]);

            self::send_mail($dealer_data->email, 'Добавленный лид #' . $lead . ' пересекается c добавленным ранее лидом', "Добавленный лид #" . $lead . " совпадает с другим\n#" . $recurring_lead . " от " . $recurring_lead_data->created_at . " " . $recurring_lead_data->name . " " . $recurring_lead_data->phone . "\nПересечение: " . $fields);
        }

        if ($dealer_data->tg_notification_send == 1 ){
            $recurring_lead_data = Lead::findOne(['id' => $recurring_lead]);

            require_once($_SERVER['DOCUMENT_ROOT'] . '/bot/index.php');

            send('sendMessage', ['text' => "Добавленный лид #" . $lead . " совпадает с другим\n#" . $recurring_lead . " от " . $recurring_lead_data->created_at . " " . $recurring_lead_data->name . " " . $recurring_lead_data->phone . "\nПересечение: " . $fields, 'chat_id' => $dealer_data->tg_notification]);
        }
        

        $stmt = $CONNECTION->prepare("SELECT user_id FROM lead WHERE id = '$recurring_lead'");
        $stmt->execute();
        $dealer = $stmt->get_result()->fetch_all(MYSQLI_ASSOC)[0]['user_id'];

        $stmt = $CONNECTION->prepare("INSERT INTO notification (dealer, recurring_lead, lead, type, fields) VALUES ('$dealer', '$lead', '$recurring_lead', 'reference', '$fields')");
        $stmt->execute();

        $dealer_data = Dealer::findOne(['user_id' => $dealer]); 

        if ($dealer_data->email_notification == 1 ){
            $lead_data = Lead::findOne(['id' => $lead]);

            self::send_mail($dealer_data->email, 'Ваш лид #' . $recurring_lead . ' упоминается в другом лиде', "Ваш лид #" . $recurring_lead . " упоминается в другом лиде\n#" . $lead . " от " . $lead_data->created_at . " " . $lead_data->name . " " . $lead_data->phone . "\nПересечение: " . $fields);
        }

        if ($dealer_data->tg_notification_send == 1 ){
            $lead_data = Lead::findOne(['id' => $lead]);

            require_once($_SERVER['DOCUMENT_ROOT'] . '/bot/index.php');

            send('sendMessage', ['text' => "Ваш лид #" . $recurring_lead . " упоминается в другом лиде\n#" . $lead . " от " . $lead_data->created_at . " " . $lead_data->name . " " . $lead_data->phone . "\nПересечение: " . $fields, 'chat_id' => $dealer_data->tg_notification]);
        }    

        return $fields;
    }

    public static function get($dealer_id) {
        global $CONNECTION;

        $stmt = $CONNECTION->prepare('SELECT * FROM notification WHERE dealer = ' . $dealer_id . ' AND reading = 0 ORDER BY id DESC');
        $stmt->execute();

        $notification_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        /*$stmt = $CONNECTION->prepare("SELECT notification.* FROM lead LEFT JOIN notification ON lead.id = notification.recurring_lead WHERE lead.user_id = " . $dealer_id);
        $stmt->execute();
        $notification_data['reference'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);*/
        return $notification_data;
    }

    public static function get_by_id($id) {
        global $CONNECTION;

        $stmt = $CONNECTION->prepare('SELECT * FROM notification WHERE id = ' . $id);
        $stmt->execute();

        $notification_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC)[0];

        /*$stmt = $CONNECTION->prepare("SELECT notification.* FROM lead LEFT JOIN notification ON lead.id = notification.recurring_lead WHERE lead.user_id = " . $dealer_id);
        $stmt->execute();
        $notification_data['reference'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);*/
        return $notification_data;
    }

    public static function get_status_channel($id, $channel) {
        global $CONNECTION;
        
        $channel_status = 0;

        if ($channel == 'email_notification') {
            $stmt = $CONNECTION->prepare('SELECT email_notification FROM dealer WHERE user_id = ' . $id);
            $stmt->execute();
            $channel_status = $stmt->get_result()->fetch_array()[0];
        }

        if ($channel == 'telegram_notification') {
            $stmt = $CONNECTION->prepare('SELECT tg_notification_send FROM dealer WHERE user_id = ' . $id);
            $stmt->execute();
            $channel_status = $stmt->get_result()->fetch_array()[0];
        }

        return $channel_status;
    }

    public static function change_channel($id, $channel) {
        global $CONNECTION;

        $channel_status = self::get_status_channel($id, $channel);

        if ($channel == 'email_notification') {
            if ($channel_status == 1) {
               $stmt = $CONNECTION->prepare('UPDATE dealer SET email_notification = 0 WHERE user_id = ' . $id); 
            } else {
                $stmt = $CONNECTION->prepare('UPDATE dealer SET email_notification = 1 WHERE user_id = ' . $id); 
            }
            $stmt->execute();
        }

        if ($channel == 'telegram_notification') {
            if ($channel_status == 1) {
               $stmt = $CONNECTION->prepare('UPDATE dealer SET tg_notification_send = 0 WHERE user_id = ' . $id); 
            } else {
                $stmt = $CONNECTION->prepare('UPDATE dealer SET tg_notification_send = 1 WHERE user_id = ' . $id); 
            }
            $stmt->execute();
        }

        return self::get_status_channel($id, $channel);
    }

    public static function close_notification($id) {
        global $CONNECTION;

        $stmt = $CONNECTION->prepare('UPDATE notification SET reading = 1 WHERE id = ' . $id);
        $stmt->execute();

        return $id;
    }

    public static function delete_notification($options) {
        global $CONNECTION;

        $query = 'DELETE FROM notification WHERE';

        foreach ($options as $key => $option) {
            $query.= '(' . $option[0] . ' = ' . $option[1] . ')';

            if ($key < count($options) - 1) {
                $query.= ' AND ';
            }
        }

        $stmt = $CONNECTION->prepare($query);
        $stmt->execute();

        return $true;
    }

    public static function read_notification($id) {
        global $CONNECTION;

        $query = 'UPDATE dealer SET new_notification = 0 WHERE id = ' . $id;

        $stmt = $CONNECTION->prepare($query);
        $stmt->execute();

        return $id;
    }
}

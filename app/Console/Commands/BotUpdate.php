<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BotUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bot:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'bot update';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $flag = true;
        $offset = 0;
        while ($flag) {
            $json = file_get_contents("https://api.telegram.org/bot".env("TELEGRAM_ACCESS_TOKEN")."/getUpdates?offset={$offset}");

            $data = json_decode($json, true);

            foreach ($data['result'] as $message) {
                $offset = $message['update_id'] + 1;
                $chatId = $message['message']['chat']['id'];
                $text = $message['message']['text'];
                $order = \App\Models\Order::where('order_id', $text)->first();
                if ($order === null) {
                    $text = 'There is no such order';
                }
                if ($order !== null) {
                    switch ($order->status) {
                        case '1':
                            $text = 'Your package from ' . $order->title . ' has not been sent yet';
                            break;
                        case '2':
                            $text = 'Your package from ' . $order->title . ' is on the way';
                            break;
                        case '3':
                            $text = 'Your package from ' . $order->title . ' has arrived';
                            break;
                    }
                }
                file_get_contents("https://api.telegram.org/bot".env("TELEGRAM_ACCESS_TOKEN")."/sendMessage?chat_id={$chatId}&text={$text}");
                sleep(2);
            }
        }
    }
}

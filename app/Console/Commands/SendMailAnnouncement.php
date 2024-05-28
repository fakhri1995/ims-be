<?php

namespace App\Console\Commands;

use App\AnnouncementMail;
use App\AnnouncementMailResult;
use App\Mail\SendAnnouncementMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendMailAnnouncement extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:send-mail-announcement {page}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return mixed
     */
    public function handle()
    {
        $page = (int) $this->argument('page') ?? 1;
        $rows = 10;

        $data = AnnouncementMail::query()
            ->with(['staff', 'group.groups.users', 'announcement.thumbnailImage'])
            ->where('publish_at', '<=', date('Y-m-d H:i:s'))
            ->where('is_send', false)
            ->simplePaginate($rows, ['*'], 'page', $page);

        $error = [];

        foreach ($data as $item) {
            if (count($item->staff)) {
                foreach ($item->staff as $sub) {
                    try {
                        $user = $sub->user;
                        if ($user) {
                            Mail::to($user->email)->send(new SendAnnouncementMail($item->announcement));
                        }
                    } catch (\Throwable $th) {
                        //throw $th;
                        $error[] = $user->name;
                    }
                }
            } else if (count($item->group)) {
                foreach ($item->group as $sub) {
                    if ($sub->groups) {
                        $group = $sub->groups;
                        if (count($group->users)) {
                            foreach ($group->users as $user) {
                                try {
                                    Mail::to($user->email)->send(new SendAnnouncementMail($item->announcement));
                                } catch (\Throwable $th) {
                                    // throw $th;
                                    $error[] = $user->name;
                                }
                            }
                        }
                    }
                }
            }

            $description = 'Pesan sukses terkirim ke penerima.';

            if (count($error)) {
                $person = implode(', ', $error);
                $description = 'Gagal kirim pesan ke email ' . $person . '.';
            }

            $result = new AnnouncementMailResult();
            $result->announcement_mail_id = $item->id;
            $result->description = $description;
            $result->save();

            $item->is_send = true;
            $item->save();
        }
    }
}

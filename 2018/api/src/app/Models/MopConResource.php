<?php
namespace MopConApi2018\App\Models;

use MopCon2018\Utils\GoogleDocsSpreadsheet;
use MopCon2018\Utils\Base;

class MopConResource
{
    private static $activityDate = ['day1' => '2018-11-03', 'day2' => '2018-11-04'];

    public static function getScheduleUnconf()
    {
        $apiData = new GoogleDocsSpreadsheet(
            static::getSourceInfo()['googleSheet']['schedule-unconf']['sheetKey'],
            static::getSourceInfo()['googleSheet']['schedule-unconf']['columns'],
            static::getSourceInfo()['googleSheet']['schedule-unconf']['sheetGridId']
        );

        $scheduleUnconfData = [];

        foreach ($apiData->toArray() as $rowIndex => $rowData) {
            foreach ($rowData as $columnName => $columnVal) {
                preg_match('/^(.*)_([0-9]+)/', $columnName, $result);
                if (!$result) {
                    $date = self::$activityDate['day1'];
                    if ($columnName == 'period') {
                        $columnName = 'duration';
                    }
                    $scheduleUnconfData[$date][$rowIndex][$columnName] = $columnVal;
                } else {
                    $theDay = 'day' . $result[2];
                    $date = self::$activityDate[$theDay];
                    $columnNameNew = $result[1];
                    if ($columnNameNew == 'period') {
                        $columnNameNew = 'duration';
                    }
                    $scheduleUnconfData[$date][$rowIndex][$columnNameNew] = $columnVal;
                }

                if (empty($scheduleUnconfData[$date][$rowIndex]['speaker'])) {
                    $scheduleUnconfData[$date][$rowIndex]['type'] = 'others';
                } else {
                    $scheduleUnconfData[$date][$rowIndex]['type'] = 'topic';
                }
            }
        }

        $scheduleUnconf = [];
        foreach ($scheduleUnconfData as $date => $items) {
            $scheduleUnconf[] = compact('date', 'items');
        }

        return $scheduleUnconf;
    }

    public static function getSchedule($fullUrlToAssets = null)
    {
        $speakers = self::getSpeaker($fullUrlToAssets);

        $apiData = new GoogleDocsSpreadsheet(
            static::getSourceInfo()['googleSheet']['schedule']['sheetKey'],
            static::getSourceInfo()['googleSheet']['schedule']['columns'],
            static::getSourceInfo()['googleSheet']['schedule']['sheetGridId']
        );

        $schedule = [];

        foreach ($apiData->toRows() as $key => $item) {
            // 跳過議會廳欄位
            if ($key === 0) {
                continue;
            }

            // R1 day1
            if (isset($item->{'gsx$_cokwr'}->{'$t'})) {
                $id = intval($item->{'gsx$_cokwr'}->{'$t'}) > 0
                    ? $item->{'gsx$_cokwr'}->{'$t'}
                    : 'day1:' . $item->{'gsx$_cokwr'}->{'$t'} . '@@@' . $item->{'gsx$day1'}->{'$t'};
                $schedule[$id] = [
                    'date' => self::$activityDate['day1'],
                    'schedule_id' => $item->{'gsx$_cokwr'}->{'$t'},
                    'duration' => $item->{'gsx$day1'}->{'$t'},
                    'location' => 'R1: 一廳',
                ];
            }

            // R2 day1
            if (isset($item->{'gsx$_cpzh4'}->{'$t'})) {
                $schedule[$item->{'gsx$_cpzh4'}->{'$t'}] = [
                    'date' => self::$activityDate['day1'],
                    'schedule_id' => $item->{'gsx$_cpzh4'}->{'$t'},
                    'duration' => $item->{'gsx$day1'}->{'$t'},
                    'location' => 'R2: 二廳',
                ];
            }

            // R3 day1
            if (isset($item->{'gsx$_cre1l'}->{'$t'})) {
                $schedule[$item->{'gsx$_cre1l'}->{'$t'}] = [
                    'date' => self::$activityDate['day1'],
                    'schedule_id' => $item->{'gsx$_cre1l'}->{'$t'},
                    'duration' => $item->{'gsx$day1'}->{'$t'},
                    'location' => 'R3: 三廳',
                ];
            }

            // R1 day2
            if (isset($item->{'gsx$_ckd7g'}->{'$t'})) {
                $id = intval($item->{'gsx$_ckd7g'}->{'$t'}) > 0
                    ? $item->{'gsx$_ckd7g'}->{'$t'}
                    : 'day2:' . $item->{'gsx$_ckd7g'}->{'$t'} . '@@@' . $item->{'gsx$day2'}->{'$t'};

                $schedule[$id] = [
                    'date' => self::$activityDate['day2'],
                    'schedule_id' => $item->{'gsx$_ckd7g'}->{'$t'},
                    'duration' => $item->{'gsx$day2'}->{'$t'},
                    'location' => 'R1: 一廳',
                ];
            }

            // R2 day2
            if (isset($item->{'gsx$_clrrx'}->{'$t'})) {
                $schedule[$item->{'gsx$_clrrx'}->{'$t'}] = [
                    'date' => self::$activityDate['day2'],
                    'schedule_id' => $item->{'gsx$_clrrx'}->{'$t'},
                    'duration' => $item->{'gsx$day2'}->{'$t'},
                    'location' => 'R2: 二廳',
                ];
            }

            // R3 day2
            if (isset($item->{'gsx$_cyevm'}->{'$t'})) {
                $schedule[$item->{'gsx$_cyevm'}->{'$t'}] = [
                    'date' => self::$activityDate['day2'],
                    'schedule_id' => $item->{'gsx$_cyevm'}->{'$t'},
                    'duration' => $item->{'gsx$day2'}->{'$t'},
                    'location' => 'R3: 三廳',
                ];
            }
        }

        // $schedule_column = [

        // ];

        foreach ($schedule as $id => $value) {
            if (strpos($id, '@@@') !== false) {
                $schedule[$id]['schedule_topic'] = $schedule[$id]['schedule_id'];
                $schedule[$id]['schedule_topic_en'] = $schedule[$id]['schedule_id'];
                $schedule[$id]['schedule_id'] = null;
                $schedule[$id]['location'] = '';
                continue;
            }
            $speakerOfschedule = array_filter($speakers, function ($speaker) use ($id) {
                return $speaker['schedule_id'] == $id;
            });
            $tmp = array_pop($speakerOfschedule) ?: [];
            foreach ($tmp as $prop => $value) {
                $schedule[$id][$prop] = $value;
            }
        }
        ksort($schedule);

        return $schedule;
    }

    public static function getSpeaker($fullUrlToAssets = null)
    {
        $apiData = new GoogleDocsSpreadsheet(
            static::getSourceInfo()['googleSheet']['speaker']['sheetKey'],
            static::getSourceInfo()['googleSheet']['speaker']['columns'],
            static::getSourceInfo()['googleSheet']['speaker']['sheetGridId']
        );

        $apiDataArray = $apiData->toArray();

        foreach ($apiDataArray as $key => &$value) {
            if (!empty($value['picture']) && $fullUrlToAssets) {
                $value['picture'] = $fullUrlToAssets . '/images/speaker/' . $value['picture'];
            }

            if (!empty($value['picture_merged']) && $fullUrlToAssets) {
                $value['picture_merged'] = $fullUrlToAssets . '/images/speaker/' . $value['picture_merged'];
            }
        }

        return $apiDataArray;
    }

    public static function getSourceInfo()
    {
        return [
            'googleSheet' => [
                'code-of-conduct' => [
                    'sheetKey' => Base::getConfig()['2018']['sheetKey'],
                    'sheetGridId' => '',
                    'columns' => [
                        'title' => 'title',
                        'content' => 'content',
                        'en_title' => 'en-title',
                        'en_content' => 'en-content',
                    ],
                    'description' => '行動準則',
                    'status' => 1,
                ],
                'schedule' => [
                    'sheetKey' => Base::getConfig()['2018']['sheetKey'],
                    'sheetGridId' => Base::getConfig()['2018']['sheetGridId']['schedule'],
                    'columns' => [],
                    'description' => '議程',
                    'status' => 1,
                ],
                'speaker' => [
                    'sheetKey' => Base::getConfig()['2018']['sheetKey'],
                    'sheetGridId' => Base::getConfig()['2018']['sheetGridId']['speaker'],
                    'columns' => [
                        'speaker_id' => '講者編號',
                        'schedule_id' => '議程編號',
                        'name' => '姓名',
                        'name_en' => '姓名en',
                        'company' => '公司',
                        'job' => '職稱',
                        'info' => '個人介紹',
                        'info_en' => '個人介紹en',
                        'picture' => '照片',
                        'filename' => '檔名',
                        'facebook' => 'facebook',
                        'github' => 'github',
                        'blog' => 'blog',
                        'website' => 'website',
                        'linkedin' => 'linkedin',
                        'type' => 'type',
                        'schedule_topic' => '議程主題',
                        'schedule_topic_en' => '議程主題en',
                        'category' => '議程類別',
                        'degree_of_difficulty' => '議程難易度',
                        'schedule_info' => '議程摘要',
                        'schedule_info_en' => '議程摘要en',
                        'characters' => '字數全形240',
                        'slide' => 'slide',
                        'picture_merged' => '合併講者照片',
                        'video_record' => '禁止錄影',
                    ],
                    'description' => '講者資料',
                    'status' => 1,
                ],
                'schedule-unconf' => [
                    'sheetKey' => Base::getConfig()['2018']['sheetKey'],
                    'sheetGridId' => Base::getConfig()['2018']['sheetGridId']['schedule-unconf'],
                    'columns' => [
                        'period' => '時段',
                        'topic' => '主題',
                        'speaker' => '講者',
                        'period_2' => '時段_2',
                        'topic_2' => '主題_2',
                        'speaker_2' => '講者_2',
                    ],
                    'description' => '交流場次',
                    'status' => 1,
                ],
                'sponsor' => [
                    'sheetKey' => Base::getConfig()['2018']['sheetKey'],
                    'sheetGridId' => Base::getConfig()['2018']['sheetGridId']['sponsor'],
                    'columns' => [
                        'order' => '贊助商編號',
                        'id' => 'id',
                        'type' => '贊助商等級',
                        'name' => '名稱',
                        'name_en' => '名稱en',
                        'info' => '介紹',
                        'info_en' => '介紹en',
                        'website' => '網址',
                        'logo' => '照片',
                        'liaison' => '窗口',
                        'remarks' => 'remarks',
                    ],
                    'description' => '贊助商',
                    'status' => 1,
                ],
                'community' => [
                    'sheetKey' => Base::getConfig()['2018']['sheetKey'],
                    'sheetGridId' => Base::getConfig()['2018']['sheetGridId']['community'],
                    'columns' => [
                        'title' => '社群名稱',
                        'id' => 'id',
                        'logo' => 'logo',
                        'liasion' => '主要聯絡人',
                        'email' => '聯絡email',
                        'info' => '中文介紹',
                        'info_en' => '英文介紹',
                        'facebook' => 'facebook',
                        'other_links' => '其他連結',
                    ],
                    'description' => '主辦社群',
                    'status' => 1,
                ],
                'volunteer' => [
                    'sheetKey' => Base::getConfig()['2018']['sheetKey'],
                    'sheetGridId' => Base::getConfig()['2018']['sheetGridId']['volunteer'],
                    'columns' => [
                        'id' => 'id',
                        'groupname' => '組別中文名稱',
                        'groupname_en' => '組別英文名稱',
                        'info' => '中文介紹字內',
                        'info_en' => '英文介紹',
                        'memberlist' => '組員名單不限中英文',
                    ],
                    'description' => '志工組織',
                    'status' => 1,
                ],
                'carousel' => [
                    'sheetKey' => Base::getConfig()['2018']['sheetKey'],
                    'sheetGridId' => Base::getConfig()['2018']['sheetGridId']['carousel'],
                    'columns' => [
                        'id' => 'id',
                        'title' => 'title',
                        'banner' => 'banner',
                        'link' => 'link'
                    ],
                    'description' => '廣告',
                    'status' => 1,
                ],
                'news' => [
                    'sheetKey' => Base::getConfig()['2018']['sheetKey'],
                    'sheetGridId' => Base::getConfig()['2018']['sheetGridId']['news'],
                    'columns' => [
                        'id' => 'id',
                        'time' => 'time',
                        'title' => 'title',
                        'description' => 'description',
                        'link' => 'link'
                    ],
                    'description' => '最新消息',
                    'status' => 1,
                ],
            ]
        ];
    }
}

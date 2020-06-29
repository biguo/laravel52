<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VideoLiker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'VideoLiker';

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
        $sourceArr = [1,6,9,10,11,13,14,15,16,17,18,20,21,22,23,24,25,28,29,30,31,32,33,34,35,36,37,38,39,40];
        $midArr = [26102,26101,26099,26098,26097,26095,26093,26092,26091,26090,26089,26088,26086,26085,26084,26082,26079,26077,26076,26074,26073,26072,26071,26070,26069,26068,26067,26066,26065,26064,26063,26062,26061,26060,26059,26058,26057,26056,26053,26052,26051,26050,26049,26048,26047,26046,26045,26044,26043,26042,26041,26040,26039,26038,26037,26036,26035,26034,26033,26032,26031,26030,26029,26028,26027,26026,26025,26024,26023,26022,26021,26020,26019,26018,26017,26016,26015,26014,26013,26012,26011,26010,26009,26008,26007,26006,26005,26004,26003,26002,26001,26000,25999,25998,25997,25996,25994,25993,25992,25991,25990,25989,25988,25986,25985,25984,25983,25982,25981,25979,25977,25976,25975,25974,25973,25972,25971,25970,25969,25968,25967,25966,25965,25964,25962,25961,25960,25959,25957,25955,25954,25953,25951,25950,25949,25948,25947,25946,25945,25944,25943,25942,25941,25940,25939,25938,25937,25936,25933,25932,25930,25929,25928,25927,25926,25925,25924,25923,25922,25921,25920,25918,25917,25916,25914,25913,25912,25911,25910,25908,25907,25906,25905,25904,25903,25902,25901,25900,25898,25894,25893,25892,25891,25890,25889,25888,25885,25884,25883,25882,25880,25879,25878,25877,25874,25873,25872,25871,25869,25868,25867,25866,25865,25864,25862,25860,25859,25858,25857,25856,25852,25851,25850,25849,25848,25847,25846,25843,25842,25841,25840,25839,25838,25837,25836,25834,25833,25829,25828,25827,25826,25825,25823,25822,25821,25820,25819,25817,25816,25814,25812,25811,25810,25809,25808,25807,25806,25805,25804,25803,25802,25800,25798,25797,25796,25795,25791,25790,25789,25788,25787,25786,25785,25783,25782,25781,25780,25779,25778,25777,25774,25771,25769,25768,25767,25766,25764,25763,25762,25761,25760,25758,25756,25750,25749,25748,25746,25745,25742,25735,25731,25730,25729,25725,25724,25723,25721,25720,25719,25714,25710,25704,25703,25702,25699,25694,25692,25690,25689,25684,25683,25682,25678,25674,25672,25671,25670,25669,25668,25667,25666,25665,25663,25662,25661,25660,25659,25656,25651,25647,25646,25644,25640,25638,25637,25636,25634,25633,25631,25630,25629,25627,25626,25625,25624,25623,25621,25620,25618,25617,25603,25599,25598,25593,25590,25580,25568,25566,25565,25564,25561,25556,25555,25554,25553,25549,25548,25545,25544,25543,25539,25537,25536,25535,25534,25532,25529,25528,25527,25523,25521,25519,25518,25517,25513,25510,25509,25504,25503,25502,25499,25498,25494,25493,25490,25489,25488,25486,25485,25484,25483,25482,25481,25480,25479,25478,25475,25474,25473,25471,25469,25468,25467,25462,25461,25458,25457,25456,25455,25453,25452,25451,25450,25449,25447,25446,25445,25444,25443,25442,25438,25437,25434,25432,25431,25429,25428,25427,25425,25422,25421,25420,25419,25414,25412,25409,25408,25407,25406,25405,25404,25403,25401,25399,25397,25396,25395,25392,25390,25388,25384,25382,25380,25377,25372,25371,25366,25365,25362,25361,25360,25358,25357,25356,25353,25352,25351,25350,25348,25347,25345,25341,25340,25339,25338,25336,25335,25334,25330,25329,25326,25323,25322,25320,25319,25318,25317,25314,25313,25311,25310,25309,25303,25302,25301,25300,25299,25298,25297,25295,25292,25291,25290,25289,25288,25287,25286,25283,25282,25277,25276,25275,25274,25273,25272,25271,25269,25268,25267,25263,25255,25254,25253,25252,25251,25249,25247,25246,25245,25240,25239,25237,25235,25234,25231,25229,25228,25227,25225,25223,25222,25221,25218,25216,25213,25211,25210,25209,25207,25206,25205,25201,25197,25194,25192,25191,25190,25185,25181,25180,25179,25178,25177,25176,25173,25169,25167,25162,25160,25156,25154,25153,25152,25151,25149,25148,25147,25146,25142,25139,25138,25136,25135,25134,25133,25129,25127,25120,25119,25117,25116,25115,25114,25112,25111,25110,25109,25104,25101,25100,25099,25098,25097,25096,25095,25091,25090,25089,25087,25086,25085,25084,25083,25082,25081,25080,25079,25078,25077,25076,25075,25074,25073,25072,25071,25070,25069,25068,25067,25066,25065,25064,25063,25062,25061,25060,25059,25058,25057,25056,25055,25054,25053,25052,25051,25050,25049,25048,25047,25046,25045,25044,25043,25042,25041,25040,25038,25037,25036,25035,25034,25033,25032,25030,25028,25027,25026,25025,25024,25023,25022,25021,25020,25019,25018,25017,25016,25015,25014,25013,25012,25011,25010,25008,25007,25005,25004,25002,25001,25000,24999,24998,24997,24996,24995,24994,24993,24992,24991,24990,24989,24988,24987,24986,24985,24984,24983,24982,24981,24980,24979,24978,24977,24976,24975,24974,24973,24972,24971,24970,24968,24967,24966,24965,24964,24963,24962,24961,24960,24959,24958,24957,24956,24955,24954,24953,24952,24951,24950,24949,24948,24947,24946,24945,24944,24943,24942,24941,24940,24939,24938,24937,24936,24935,24934,24933,24932,24931,24929,24928,24927,24926,24925,24924,24923,24922,24921,24920,24919,24918,24917,24916,24915,24914,24913,24912,24909,24908,24907,24906,24905,24904,24903,24902,24901,24900,24899,24898,24897,24896,24895,24894,24893,24892,24891,24890,24889,24888,24887,24886,24885,24884,24883,24882,24881,24880,24879,24878,24877,24876,24875,24874,24873,24872,24871,24870,24869,24868,24867,24866,24865,24864,24863,24862,24861,24860,24859,24858,24857,24856,24855,24854,24853,24852,24851,24850,24849,24848,24847,24846,24845,24844,24843,24842,24841,24840,24839,24838,24837,24836,24835,24834,24833,24832,24831,24830,24829,24828,24827,24823,24822,24821,24820,24819,24818,24817,24816,24815,24814,24813,24812,24811,24810,24809,24808,24807,24806,24805,24804,24802,24801,24799,24798,24796,24795,24794,24793,24790,24789,24787,24786,24785,24784,24783,24781,24780,24778,24777,24771,24766,24765,24764,24760,24758,24754,24753,24751,24749,24748,24747,24745,24743,24741,24740,24739,24738,24737,24736,24734,24733,24732,24731,24730,24728,24727,24726,24725,24724,24723,24719,24713,24712,24711,24710,24706,24705,24704,24702,24701,24700,24699,24698,24696,24695,24694,24693,24692,24691,24690,24689,24688,24687,24686,24685,24682,24679,24677,24676,24675,24674,24672,24671,24670,24669,24668,24666,24664,24662,24661,24648,24646,24645,24643,24638,24631,24629,24628,24627,24626,24625,24621,24619,24618,24615,24614,24613];
        foreach ($sourceArr as $item){
            for ($i = 0; $i< mt_rand(20,50); $i++){
                $sql = "insert into video_like(source_id, type, mid)  values ($item, 1, ".$midArr[mt_rand(0,count($midArr)-1)].");";
                DB::select($sql);
            }
        }
        echo "Complete!";
    }
}

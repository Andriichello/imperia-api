<?php

namespace Database\Seeders\Voyager;

use Illuminate\Database\Seeder;

class VoyagerPermissionRoleTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('permission_role')->delete();

        \DB::table('permission_role')->insert(array (
            0 =>
            array (
                'permission_id' => 1,
                'role_id' => 1,
            ),
            1 =>
            array (
                'permission_id' => 1,
                'role_id' => 3,
            ),
            2 =>
            array (
                'permission_id' => 1,
                'role_id' => 4,
            ),
            3 =>
            array (
                'permission_id' => 1,
                'role_id' => 6,
            ),
            4 =>
            array (
                'permission_id' => 1,
                'role_id' => 8,
            ),
            5 =>
            array (
                'permission_id' => 2,
                'role_id' => 1,
            ),
            6 =>
            array (
                'permission_id' => 2,
                'role_id' => 3,
            ),
            7 =>
            array (
                'permission_id' => 3,
                'role_id' => 1,
            ),
            8 =>
            array (
                'permission_id' => 3,
                'role_id' => 3,
            ),
            9 =>
            array (
                'permission_id' => 4,
                'role_id' => 1,
            ),
            10 =>
            array (
                'permission_id' => 4,
                'role_id' => 3,
            ),
            11 =>
            array (
                'permission_id' => 5,
                'role_id' => 1,
            ),
            12 =>
            array (
                'permission_id' => 5,
                'role_id' => 3,
            ),
            13 =>
            array (
                'permission_id' => 6,
                'role_id' => 1,
            ),
            14 =>
            array (
                'permission_id' => 6,
                'role_id' => 3,
            ),
            15 =>
            array (
                'permission_id' => 7,
                'role_id' => 1,
            ),
            16 =>
            array (
                'permission_id' => 7,
                'role_id' => 3,
            ),
            17 =>
            array (
                'permission_id' => 8,
                'role_id' => 1,
            ),
            18 =>
            array (
                'permission_id' => 8,
                'role_id' => 3,
            ),
            19 =>
            array (
                'permission_id' => 9,
                'role_id' => 1,
            ),
            20 =>
            array (
                'permission_id' => 9,
                'role_id' => 3,
            ),
            21 =>
            array (
                'permission_id' => 10,
                'role_id' => 1,
            ),
            22 =>
            array (
                'permission_id' => 10,
                'role_id' => 3,
            ),
            23 =>
            array (
                'permission_id' => 11,
                'role_id' => 1,
            ),
            24 =>
            array (
                'permission_id' => 11,
                'role_id' => 3,
            ),
            25 =>
            array (
                'permission_id' => 11,
                'role_id' => 6,
            ),
            26 =>
            array (
                'permission_id' => 12,
                'role_id' => 1,
            ),
            27 =>
            array (
                'permission_id' => 12,
                'role_id' => 3,
            ),
            28 =>
            array (
                'permission_id' => 12,
                'role_id' => 6,
            ),
            29 =>
            array (
                'permission_id' => 13,
                'role_id' => 1,
            ),
            30 =>
            array (
                'permission_id' => 13,
                'role_id' => 3,
            ),
            31 =>
            array (
                'permission_id' => 13,
                'role_id' => 6,
            ),
            32 =>
            array (
                'permission_id' => 14,
                'role_id' => 1,
            ),
            33 =>
            array (
                'permission_id' => 14,
                'role_id' => 3,
            ),
            34 =>
            array (
                'permission_id' => 14,
                'role_id' => 6,
            ),
            35 =>
            array (
                'permission_id' => 15,
                'role_id' => 1,
            ),
            36 =>
            array (
                'permission_id' => 15,
                'role_id' => 3,
            ),
            37 =>
            array (
                'permission_id' => 15,
                'role_id' => 6,
            ),
            38 =>
            array (
                'permission_id' => 16,
                'role_id' => 1,
            ),
            39 =>
            array (
                'permission_id' => 16,
                'role_id' => 3,
            ),
            40 =>
            array (
                'permission_id' => 16,
                'role_id' => 6,
            ),
            41 =>
            array (
                'permission_id' => 17,
                'role_id' => 1,
            ),
            42 =>
            array (
                'permission_id' => 17,
                'role_id' => 3,
            ),
            43 =>
            array (
                'permission_id' => 17,
                'role_id' => 6,
            ),
            44 =>
            array (
                'permission_id' => 18,
                'role_id' => 1,
            ),
            45 =>
            array (
                'permission_id' => 18,
                'role_id' => 3,
            ),
            46 =>
            array (
                'permission_id' => 18,
                'role_id' => 6,
            ),
            47 =>
            array (
                'permission_id' => 19,
                'role_id' => 1,
            ),
            48 =>
            array (
                'permission_id' => 19,
                'role_id' => 3,
            ),
            49 =>
            array (
                'permission_id' => 19,
                'role_id' => 6,
            ),
            50 =>
            array (
                'permission_id' => 20,
                'role_id' => 1,
            ),
            51 =>
            array (
                'permission_id' => 20,
                'role_id' => 3,
            ),
            52 =>
            array (
                'permission_id' => 20,
                'role_id' => 6,
            ),
            53 =>
            array (
                'permission_id' => 21,
                'role_id' => 1,
            ),
            54 =>
            array (
                'permission_id' => 21,
                'role_id' => 3,
            ),
            55 =>
            array (
                'permission_id' => 22,
                'role_id' => 1,
            ),
            56 =>
            array (
                'permission_id' => 22,
                'role_id' => 3,
            ),
            57 =>
            array (
                'permission_id' => 23,
                'role_id' => 1,
            ),
            58 =>
            array (
                'permission_id' => 23,
                'role_id' => 3,
            ),
            59 =>
            array (
                'permission_id' => 24,
                'role_id' => 1,
            ),
            60 =>
            array (
                'permission_id' => 24,
                'role_id' => 3,
            ),
            61 =>
            array (
                'permission_id' => 25,
                'role_id' => 1,
            ),
            62 =>
            array (
                'permission_id' => 25,
                'role_id' => 3,
            ),
            63 =>
            array (
                'permission_id' => 26,
                'role_id' => 1,
            ),
            64 =>
            array (
                'permission_id' => 26,
                'role_id' => 3,
            ),
            65 =>
            array (
                'permission_id' => 27,
                'role_id' => 1,
            ),
            66 =>
            array (
                'permission_id' => 27,
                'role_id' => 3,
            ),
            67 =>
            array (
                'permission_id' => 27,
                'role_id' => 4,
            ),
            68 =>
            array (
                'permission_id' => 27,
                'role_id' => 6,
            ),
            69 =>
            array (
                'permission_id' => 27,
                'role_id' => 8,
            ),
            70 =>
            array (
                'permission_id' => 28,
                'role_id' => 1,
            ),
            71 =>
            array (
                'permission_id' => 28,
                'role_id' => 3,
            ),
            72 =>
            array (
                'permission_id' => 28,
                'role_id' => 4,
            ),
            73 =>
            array (
                'permission_id' => 28,
                'role_id' => 6,
            ),
            74 =>
            array (
                'permission_id' => 28,
                'role_id' => 8,
            ),
            75 =>
            array (
                'permission_id' => 29,
                'role_id' => 1,
            ),
            76 =>
            array (
                'permission_id' => 29,
                'role_id' => 3,
            ),
            77 =>
            array (
                'permission_id' => 29,
                'role_id' => 6,
            ),
            78 =>
            array (
                'permission_id' => 29,
                'role_id' => 8,
            ),
            79 =>
            array (
                'permission_id' => 30,
                'role_id' => 1,
            ),
            80 =>
            array (
                'permission_id' => 30,
                'role_id' => 3,
            ),
            81 =>
            array (
                'permission_id' => 30,
                'role_id' => 6,
            ),
            82 =>
            array (
                'permission_id' => 30,
                'role_id' => 8,
            ),
            83 =>
            array (
                'permission_id' => 31,
                'role_id' => 1,
            ),
            84 =>
            array (
                'permission_id' => 31,
                'role_id' => 3,
            ),
            85 =>
            array (
                'permission_id' => 31,
                'role_id' => 6,
            ),
            86 =>
            array (
                'permission_id' => 31,
                'role_id' => 8,
            ),
            87 =>
            array (
                'permission_id' => 32,
                'role_id' => 1,
            ),
            88 =>
            array (
                'permission_id' => 32,
                'role_id' => 3,
            ),
            89 =>
            array (
                'permission_id' => 32,
                'role_id' => 4,
            ),
            90 =>
            array (
                'permission_id' => 32,
                'role_id' => 6,
            ),
            91 =>
            array (
                'permission_id' => 32,
                'role_id' => 8,
            ),
            92 =>
            array (
                'permission_id' => 33,
                'role_id' => 1,
            ),
            93 =>
            array (
                'permission_id' => 33,
                'role_id' => 3,
            ),
            94 =>
            array (
                'permission_id' => 33,
                'role_id' => 4,
            ),
            95 =>
            array (
                'permission_id' => 33,
                'role_id' => 6,
            ),
            96 =>
            array (
                'permission_id' => 33,
                'role_id' => 8,
            ),
            97 =>
            array (
                'permission_id' => 34,
                'role_id' => 1,
            ),
            98 =>
            array (
                'permission_id' => 34,
                'role_id' => 3,
            ),
            99 =>
            array (
                'permission_id' => 34,
                'role_id' => 6,
            ),
            100 =>
            array (
                'permission_id' => 34,
                'role_id' => 8,
            ),
            101 =>
            array (
                'permission_id' => 35,
                'role_id' => 1,
            ),
            102 =>
            array (
                'permission_id' => 35,
                'role_id' => 3,
            ),
            103 =>
            array (
                'permission_id' => 35,
                'role_id' => 6,
            ),
            104 =>
            array (
                'permission_id' => 35,
                'role_id' => 8,
            ),
            105 =>
            array (
                'permission_id' => 36,
                'role_id' => 1,
            ),
            106 =>
            array (
                'permission_id' => 36,
                'role_id' => 3,
            ),
            107 =>
            array (
                'permission_id' => 36,
                'role_id' => 6,
            ),
            108 =>
            array (
                'permission_id' => 36,
                'role_id' => 8,
            ),
            109 =>
            array (
                'permission_id' => 37,
                'role_id' => 3,
            ),
            110 =>
            array (
                'permission_id' => 37,
                'role_id' => 4,
            ),
            111 =>
            array (
                'permission_id' => 37,
                'role_id' => 6,
            ),
            112 =>
            array (
                'permission_id' => 37,
                'role_id' => 8,
            ),
            113 =>
            array (
                'permission_id' => 38,
                'role_id' => 3,
            ),
            114 =>
            array (
                'permission_id' => 38,
                'role_id' => 4,
            ),
            115 =>
            array (
                'permission_id' => 38,
                'role_id' => 6,
            ),
            116 =>
            array (
                'permission_id' => 38,
                'role_id' => 8,
            ),
            117 =>
            array (
                'permission_id' => 39,
                'role_id' => 3,
            ),
            118 =>
            array (
                'permission_id' => 39,
                'role_id' => 6,
            ),
            119 =>
            array (
                'permission_id' => 39,
                'role_id' => 8,
            ),
            120 =>
            array (
                'permission_id' => 40,
                'role_id' => 3,
            ),
            121 =>
            array (
                'permission_id' => 40,
                'role_id' => 6,
            ),
            122 =>
            array (
                'permission_id' => 40,
                'role_id' => 8,
            ),
            123 =>
            array (
                'permission_id' => 41,
                'role_id' => 3,
            ),
            124 =>
            array (
                'permission_id' => 41,
                'role_id' => 6,
            ),
            125 =>
            array (
                'permission_id' => 41,
                'role_id' => 8,
            ),
            126 =>
            array (
                'permission_id' => 47,
                'role_id' => 3,
            ),
            127 =>
            array (
                'permission_id' => 47,
                'role_id' => 4,
            ),
            128 =>
            array (
                'permission_id' => 47,
                'role_id' => 6,
            ),
            129 =>
            array (
                'permission_id' => 47,
                'role_id' => 8,
            ),
            130 =>
            array (
                'permission_id' => 48,
                'role_id' => 3,
            ),
            131 =>
            array (
                'permission_id' => 48,
                'role_id' => 4,
            ),
            132 =>
            array (
                'permission_id' => 48,
                'role_id' => 6,
            ),
            133 =>
            array (
                'permission_id' => 48,
                'role_id' => 8,
            ),
            134 =>
            array (
                'permission_id' => 49,
                'role_id' => 3,
            ),
            135 =>
            array (
                'permission_id' => 49,
                'role_id' => 6,
            ),
            136 =>
            array (
                'permission_id' => 49,
                'role_id' => 8,
            ),
            137 =>
            array (
                'permission_id' => 50,
                'role_id' => 3,
            ),
            138 =>
            array (
                'permission_id' => 50,
                'role_id' => 6,
            ),
            139 =>
            array (
                'permission_id' => 50,
                'role_id' => 8,
            ),
            140 =>
            array (
                'permission_id' => 51,
                'role_id' => 3,
            ),
            141 =>
            array (
                'permission_id' => 51,
                'role_id' => 6,
            ),
            142 =>
            array (
                'permission_id' => 51,
                'role_id' => 8,
            ),
            143 =>
            array (
                'permission_id' => 62,
                'role_id' => 3,
            ),
            144 =>
            array (
                'permission_id' => 62,
                'role_id' => 4,
            ),
            145 =>
            array (
                'permission_id' => 62,
                'role_id' => 6,
            ),
            146 =>
            array (
                'permission_id' => 62,
                'role_id' => 8,
            ),
            147 =>
            array (
                'permission_id' => 63,
                'role_id' => 3,
            ),
            148 =>
            array (
                'permission_id' => 63,
                'role_id' => 4,
            ),
            149 =>
            array (
                'permission_id' => 63,
                'role_id' => 6,
            ),
            150 =>
            array (
                'permission_id' => 63,
                'role_id' => 8,
            ),
            151 =>
            array (
                'permission_id' => 64,
                'role_id' => 3,
            ),
            152 =>
            array (
                'permission_id' => 64,
                'role_id' => 6,
            ),
            153 =>
            array (
                'permission_id' => 64,
                'role_id' => 8,
            ),
            154 =>
            array (
                'permission_id' => 65,
                'role_id' => 3,
            ),
            155 =>
            array (
                'permission_id' => 65,
                'role_id' => 6,
            ),
            156 =>
            array (
                'permission_id' => 65,
                'role_id' => 8,
            ),
            157 =>
            array (
                'permission_id' => 66,
                'role_id' => 3,
            ),
            158 =>
            array (
                'permission_id' => 66,
                'role_id' => 6,
            ),
            159 =>
            array (
                'permission_id' => 66,
                'role_id' => 8,
            ),
            160 =>
            array (
                'permission_id' => 67,
                'role_id' => 3,
            ),
            161 =>
            array (
                'permission_id' => 67,
                'role_id' => 4,
            ),
            162 =>
            array (
                'permission_id' => 67,
                'role_id' => 6,
            ),
            163 =>
            array (
                'permission_id' => 67,
                'role_id' => 8,
            ),
            164 =>
            array (
                'permission_id' => 68,
                'role_id' => 3,
            ),
            165 =>
            array (
                'permission_id' => 68,
                'role_id' => 4,
            ),
            166 =>
            array (
                'permission_id' => 68,
                'role_id' => 6,
            ),
            167 =>
            array (
                'permission_id' => 68,
                'role_id' => 8,
            ),
            168 =>
            array (
                'permission_id' => 69,
                'role_id' => 3,
            ),
            169 =>
            array (
                'permission_id' => 69,
                'role_id' => 6,
            ),
            170 =>
            array (
                'permission_id' => 69,
                'role_id' => 8,
            ),
            171 =>
            array (
                'permission_id' => 70,
                'role_id' => 3,
            ),
            172 =>
            array (
                'permission_id' => 70,
                'role_id' => 6,
            ),
            173 =>
            array (
                'permission_id' => 70,
                'role_id' => 8,
            ),
            174 =>
            array (
                'permission_id' => 71,
                'role_id' => 3,
            ),
            175 =>
            array (
                'permission_id' => 71,
                'role_id' => 6,
            ),
            176 =>
            array (
                'permission_id' => 71,
                'role_id' => 8,
            ),
            177 =>
            array (
                'permission_id' => 72,
                'role_id' => 3,
            ),
            178 =>
            array (
                'permission_id' => 72,
                'role_id' => 4,
            ),
            179 =>
            array (
                'permission_id' => 72,
                'role_id' => 6,
            ),
            180 =>
            array (
                'permission_id' => 72,
                'role_id' => 8,
            ),
            181 =>
            array (
                'permission_id' => 73,
                'role_id' => 3,
            ),
            182 =>
            array (
                'permission_id' => 73,
                'role_id' => 4,
            ),
            183 =>
            array (
                'permission_id' => 73,
                'role_id' => 6,
            ),
            184 =>
            array (
                'permission_id' => 73,
                'role_id' => 8,
            ),
            185 =>
            array (
                'permission_id' => 74,
                'role_id' => 3,
            ),
            186 =>
            array (
                'permission_id' => 74,
                'role_id' => 6,
            ),
            187 =>
            array (
                'permission_id' => 74,
                'role_id' => 8,
            ),
            188 =>
            array (
                'permission_id' => 75,
                'role_id' => 3,
            ),
            189 =>
            array (
                'permission_id' => 75,
                'role_id' => 6,
            ),
            190 =>
            array (
                'permission_id' => 75,
                'role_id' => 8,
            ),
            191 =>
            array (
                'permission_id' => 76,
                'role_id' => 3,
            ),
            192 =>
            array (
                'permission_id' => 76,
                'role_id' => 6,
            ),
            193 =>
            array (
                'permission_id' => 76,
                'role_id' => 8,
            ),
            194 =>
            array (
                'permission_id' => 77,
                'role_id' => 3,
            ),
            195 =>
            array (
                'permission_id' => 77,
                'role_id' => 4,
            ),
            196 =>
            array (
                'permission_id' => 77,
                'role_id' => 6,
            ),
            197 =>
            array (
                'permission_id' => 77,
                'role_id' => 8,
            ),
            198 =>
            array (
                'permission_id' => 78,
                'role_id' => 3,
            ),
            199 =>
            array (
                'permission_id' => 78,
                'role_id' => 4,
            ),
            200 =>
            array (
                'permission_id' => 78,
                'role_id' => 6,
            ),
            201 =>
            array (
                'permission_id' => 78,
                'role_id' => 8,
            ),
            202 =>
            array (
                'permission_id' => 79,
                'role_id' => 3,
            ),
            203 =>
            array (
                'permission_id' => 79,
                'role_id' => 6,
            ),
            204 =>
            array (
                'permission_id' => 79,
                'role_id' => 8,
            ),
            205 =>
            array (
                'permission_id' => 80,
                'role_id' => 3,
            ),
            206 =>
            array (
                'permission_id' => 80,
                'role_id' => 6,
            ),
            207 =>
            array (
                'permission_id' => 80,
                'role_id' => 8,
            ),
            208 =>
            array (
                'permission_id' => 81,
                'role_id' => 3,
            ),
            209 =>
            array (
                'permission_id' => 81,
                'role_id' => 6,
            ),
            210 =>
            array (
                'permission_id' => 81,
                'role_id' => 8,
            ),
            211 =>
            array (
                'permission_id' => 82,
                'role_id' => 3,
            ),
            212 =>
            array (
                'permission_id' => 82,
                'role_id' => 4,
            ),
            213 =>
            array (
                'permission_id' => 82,
                'role_id' => 6,
            ),
            214 =>
            array (
                'permission_id' => 82,
                'role_id' => 8,
            ),
            215 =>
            array (
                'permission_id' => 83,
                'role_id' => 3,
            ),
            216 =>
            array (
                'permission_id' => 83,
                'role_id' => 4,
            ),
            217 =>
            array (
                'permission_id' => 83,
                'role_id' => 6,
            ),
            218 =>
            array (
                'permission_id' => 83,
                'role_id' => 8,
            ),
            219 =>
            array (
                'permission_id' => 84,
                'role_id' => 3,
            ),
            220 =>
            array (
                'permission_id' => 84,
                'role_id' => 6,
            ),
            221 =>
            array (
                'permission_id' => 84,
                'role_id' => 8,
            ),
            222 =>
            array (
                'permission_id' => 85,
                'role_id' => 3,
            ),
            223 =>
            array (
                'permission_id' => 85,
                'role_id' => 6,
            ),
            224 =>
            array (
                'permission_id' => 85,
                'role_id' => 8,
            ),
            225 =>
            array (
                'permission_id' => 86,
                'role_id' => 3,
            ),
            226 =>
            array (
                'permission_id' => 86,
                'role_id' => 6,
            ),
            227 =>
            array (
                'permission_id' => 86,
                'role_id' => 8,
            ),
            228 =>
            array (
                'permission_id' => 87,
                'role_id' => 3,
            ),
            229 =>
            array (
                'permission_id' => 87,
                'role_id' => 4,
            ),
            230 =>
            array (
                'permission_id' => 87,
                'role_id' => 6,
            ),
            231 =>
            array (
                'permission_id' => 87,
                'role_id' => 8,
            ),
            232 =>
            array (
                'permission_id' => 88,
                'role_id' => 3,
            ),
            233 =>
            array (
                'permission_id' => 88,
                'role_id' => 4,
            ),
            234 =>
            array (
                'permission_id' => 88,
                'role_id' => 6,
            ),
            235 =>
            array (
                'permission_id' => 88,
                'role_id' => 8,
            ),
            236 =>
            array (
                'permission_id' => 89,
                'role_id' => 3,
            ),
            237 =>
            array (
                'permission_id' => 89,
                'role_id' => 6,
            ),
            238 =>
            array (
                'permission_id' => 89,
                'role_id' => 8,
            ),
            239 =>
            array (
                'permission_id' => 90,
                'role_id' => 3,
            ),
            240 =>
            array (
                'permission_id' => 90,
                'role_id' => 6,
            ),
            241 =>
            array (
                'permission_id' => 90,
                'role_id' => 8,
            ),
            242 =>
            array (
                'permission_id' => 91,
                'role_id' => 3,
            ),
            243 =>
            array (
                'permission_id' => 91,
                'role_id' => 6,
            ),
            244 =>
            array (
                'permission_id' => 91,
                'role_id' => 8,
            ),
            245 =>
            array (
                'permission_id' => 92,
                'role_id' => 3,
            ),
            246 =>
            array (
                'permission_id' => 92,
                'role_id' => 4,
            ),
            247 =>
            array (
                'permission_id' => 92,
                'role_id' => 6,
            ),
            248 =>
            array (
                'permission_id' => 92,
                'role_id' => 8,
            ),
            249 =>
            array (
                'permission_id' => 93,
                'role_id' => 3,
            ),
            250 =>
            array (
                'permission_id' => 93,
                'role_id' => 4,
            ),
            251 =>
            array (
                'permission_id' => 93,
                'role_id' => 6,
            ),
            252 =>
            array (
                'permission_id' => 93,
                'role_id' => 8,
            ),
            253 =>
            array (
                'permission_id' => 94,
                'role_id' => 3,
            ),
            254 =>
            array (
                'permission_id' => 94,
                'role_id' => 6,
            ),
            255 =>
            array (
                'permission_id' => 94,
                'role_id' => 8,
            ),
            256 =>
            array (
                'permission_id' => 95,
                'role_id' => 3,
            ),
            257 =>
            array (
                'permission_id' => 95,
                'role_id' => 6,
            ),
            258 =>
            array (
                'permission_id' => 95,
                'role_id' => 8,
            ),
            259 =>
            array (
                'permission_id' => 96,
                'role_id' => 3,
            ),
            260 =>
            array (
                'permission_id' => 96,
                'role_id' => 6,
            ),
            261 =>
            array (
                'permission_id' => 96,
                'role_id' => 8,
            ),
            262 =>
            array (
                'permission_id' => 97,
                'role_id' => 3,
            ),
            263 =>
            array (
                'permission_id' => 97,
                'role_id' => 4,
            ),
            264 =>
            array (
                'permission_id' => 97,
                'role_id' => 6,
            ),
            265 =>
            array (
                'permission_id' => 97,
                'role_id' => 8,
            ),
            266 =>
            array (
                'permission_id' => 98,
                'role_id' => 3,
            ),
            267 =>
            array (
                'permission_id' => 98,
                'role_id' => 4,
            ),
            268 =>
            array (
                'permission_id' => 98,
                'role_id' => 6,
            ),
            269 =>
            array (
                'permission_id' => 98,
                'role_id' => 8,
            ),
            270 =>
            array (
                'permission_id' => 99,
                'role_id' => 3,
            ),
            271 =>
            array (
                'permission_id' => 99,
                'role_id' => 6,
            ),
            272 =>
            array (
                'permission_id' => 99,
                'role_id' => 8,
            ),
            273 =>
            array (
                'permission_id' => 100,
                'role_id' => 3,
            ),
            274 =>
            array (
                'permission_id' => 100,
                'role_id' => 6,
            ),
            275 =>
            array (
                'permission_id' => 100,
                'role_id' => 8,
            ),
            276 =>
            array (
                'permission_id' => 101,
                'role_id' => 3,
            ),
            277 =>
            array (
                'permission_id' => 101,
                'role_id' => 6,
            ),
            278 =>
            array (
                'permission_id' => 101,
                'role_id' => 8,
            ),
            279 =>
            array (
                'permission_id' => 102,
                'role_id' => 3,
            ),
            280 =>
            array (
                'permission_id' => 102,
                'role_id' => 6,
            ),
            281 =>
            array (
                'permission_id' => 102,
                'role_id' => 8,
            ),
            282 =>
            array (
                'permission_id' => 103,
                'role_id' => 3,
            ),
            283 =>
            array (
                'permission_id' => 103,
                'role_id' => 6,
            ),
            284 =>
            array (
                'permission_id' => 103,
                'role_id' => 8,
            ),
            285 =>
            array (
                'permission_id' => 104,
                'role_id' => 3,
            ),
            286 =>
            array (
                'permission_id' => 104,
                'role_id' => 6,
            ),
            287 =>
            array (
                'permission_id' => 104,
                'role_id' => 8,
            ),
            288 =>
            array (
                'permission_id' => 105,
                'role_id' => 3,
            ),
            289 =>
            array (
                'permission_id' => 105,
                'role_id' => 6,
            ),
            290 =>
            array (
                'permission_id' => 105,
                'role_id' => 8,
            ),
            291 =>
            array (
                'permission_id' => 106,
                'role_id' => 3,
            ),
            292 =>
            array (
                'permission_id' => 106,
                'role_id' => 6,
            ),
            293 =>
            array (
                'permission_id' => 106,
                'role_id' => 8,
            ),
            294 =>
            array (
                'permission_id' => 107,
                'role_id' => 3,
            ),
            295 =>
            array (
                'permission_id' => 107,
                'role_id' => 6,
            ),
            296 =>
            array (
                'permission_id' => 107,
                'role_id' => 8,
            ),
            297 =>
            array (
                'permission_id' => 108,
                'role_id' => 3,
            ),
            298 =>
            array (
                'permission_id' => 108,
                'role_id' => 6,
            ),
            299 =>
            array (
                'permission_id' => 108,
                'role_id' => 8,
            ),
            300 =>
            array (
                'permission_id' => 109,
                'role_id' => 3,
            ),
            301 =>
            array (
                'permission_id' => 109,
                'role_id' => 6,
            ),
            302 =>
            array (
                'permission_id' => 109,
                'role_id' => 8,
            ),
            303 =>
            array (
                'permission_id' => 110,
                'role_id' => 3,
            ),
            304 =>
            array (
                'permission_id' => 110,
                'role_id' => 6,
            ),
            305 =>
            array (
                'permission_id' => 110,
                'role_id' => 8,
            ),
            306 =>
            array (
                'permission_id' => 111,
                'role_id' => 3,
            ),
            307 =>
            array (
                'permission_id' => 111,
                'role_id' => 6,
            ),
            308 =>
            array (
                'permission_id' => 111,
                'role_id' => 8,
            ),
        ));


    }
}

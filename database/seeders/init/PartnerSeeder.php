<?php

namespace Database\Seeders\init;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PartnerSeeder extends Seeder
{
    public function run()
    {
        DB::insert("INSERT INTO `product_partners` (`id`, `partner_category_id`, `business_id`, `name`, `short`, `street`, `city`, `zip`, `phone`, `email`, `created_at`, `updated_at`, `deleted_at`) VALUES
            (5, 1,	'31320155',	'Všeobecná úverová banka, a.s',	'VÚB',	'Mlynské nivy 1',	'Bratislava',	'82990',	NULL,	NULL,	'2024-02-22 14:09:08',	'2024-02-22 14:09:08',	NULL),

            (4, 2,	'00151700',	'Allianz - Slovenská poisťovňa, a.s.',	'Allianz',	'Pribinova 19',	'Bratislava',	'81109',	NULL,	NULL,	'2024-02-22 14:08:43',	'2024-02-22 14:08:43',	NULL),
            (13, 2,	'31325416',	'ČSOB Poisťovňa, a.s.',	'ČSOB Poisťovňa',	'Žižkova 11',	'Bratislava',	'81102',	NULL,	NULL,	'2024-02-22 14:13:36',	'2024-02-22 14:13:36',	NULL),
            (25, 2,	'54228573',	'Generali Poisťovňa, pobočka poisťovne z iného členského št.',	'Generali',	'Lamačská cesta 3/A',	'Bratislava',	'84104',	NULL,	NULL,	'2024-02-22 14:19:36',	'2024-02-22 14:19:36',	NULL),

            (33, 5,	'35901624',	'Allianz - Slovenská dôchodková správcovská spoločnosť, a.s.',	'Allianz DSS',	'Pribinova 19',	'Bratislava',	'81109',	NULL,	NULL,	'2024-02-22 14:27:05',	'2024-02-22 14:27:05',	NULL),
            (35, 5,	'35903058', 'VÚB Generali dôchodková správcovská spoločnosť, a.s.',	'VÚB Generali',	'Mlynské nivy 1',	'Bratislava',	'82004',	NULL,	NULL,	'2024-02-22 14:28:20',	'2024-02-22 14:28:20',	NULL);");

//        DB::insert("INSERT INTO `product_partners` (`id`, `partner_category_id`, `business_id`, `name`, `short`, `street`, `city`, `zip`, `phone`, `email`, `created_at`, `updated_at`, `deleted_at`) VALUES
////            (5, 1,	'31320155',	'Všeobecná úverová banka, a.s',	'VÚB',	'Mlynské nivy 1',	'Bratislava',	'82990',	NULL,	NULL,	'2024-02-22 14:09:08',	'2024-02-22 14:09:08',	NULL),
//            (14, 1,	'47251336',	'UniCredit Bank Czech Republic and Slovakia, a.s., pobočka zb',	'UCB BANK',	'Šancová 1/A',	'Bratislava',	'81333',	NULL,	NULL,	'2024-02-22 14:14:03',	'2024-02-22 14:14:03',	NULL),
//            (12, 1,	'36854140',	'Československá obchodná banka, a.s.',	'ČSOB banka',	'Žižkova 11',	'Bratislava',	'81102',	NULL,	NULL,	'2024-02-22 14:13:11',	'2024-02-22 14:13:11',	NULL),
//            (18, 1,	'00686930',	'Tatra banka, a.s.',	'TB',	'Hodžovo námestie 3',	'Bratislava',	'81106',	NULL,	NULL,	'2024-02-22 14:15:58',	'2024-02-22 14:15:58',	NULL),
//            (2, 1,	'31340890',	'365.bank, a. s.',	'365.bank',	'Dvořákovo nábrežie 4',	'Bratislava',	'81102',	NULL,	NULL,	'2024-02-22 14:05:18',	'2024-02-22 14:07:21',	NULL),
//            (22, 1,	'00151653',	'Slovenská sporiteľňa, a.s.',	'SLSP',	'Tomášikova 48',	'Bratislava',	'83237',	NULL,	NULL,	'2024-02-22 14:17:36',	'2024-02-22 14:17:36',	NULL),
//            (30, 1,	'36819638',	'mBank S.A., pobočka zahraničnej banky',	'mBANK',	'Pribinova 10',	'Bratislava',	'81109',	NULL,	NULL,	'2024-02-22 14:23:47',	'2024-02-22 14:23:47',	NULL),
//
//
//            (1, 2,	'52241289',	'YOUPLUS Životná poisťovňa, pobočka poisť. z iného čl. štátu',	'YOUPLUS',	'Mlynské nivy 5',	'Bratislava',	'82109',	NULL,	NULL,	'2024-02-22 14:03:55',	'2024-02-22 14:07:02',	NULL),
//            (3, 2,	'31383408',	'Wüstenrot poisťovňa, a.s.',	'WUSTENROT',	'Digital Park I, Einsteinova 21',	'Bratislava',	'85101',	NULL,	NULL,	'2024-02-22 14:08:13',	'2024-02-22 14:08:13',	NULL),
////            (4, 2,	'00151700',	'Allianz - Slovenská poisťovňa, a.s.',	'ALLIANZ',	'Pribinova 19',	'Bratislava',	'81109',	NULL,	NULL,	'2024-02-22 14:08:43',	'2024-02-22 14:08:43',	NULL),
//            (6, 2,	'53812948',	'UNIQA pojišťovna, a.s., pobočka poisťovne z iného čl. štátu',	'UNIQA POIS',	'Krasovského 3986/15',	'Bratislava',	'85101',	NULL,	NULL,	'2024-02-22 14:09:35',	'2024-02-22 14:09:35',	NULL),
//            (7, 2,	'50013602',	'Colonnade Insurance S.A., pobočka poisťovne z iného čl.štátu',	'COLONNADE',	'Moldavská cesta 8 B',	'Košice',	'04280',	NULL,	NULL,	'2024-02-22 14:10:31',	'2024-02-22 14:10:31',	NULL),
//            (10, 2,	'31322051',	'Union poisťovňa, a. s.',	'UNION POIS',	'Karadžičova 10',	'Bratislava',	'81360',	NULL,	NULL,	'2024-02-22 14:12:08',	'2024-02-22 14:12:08',	NULL),
////            (13, 2,	'31325416',	'ČSOB Poisťovňa, a.s.',	'ČSOB POIS',	'Žižkova 11',	'Bratislava',	'81102',	NULL,	NULL,	'2024-02-22 14:13:36',	'2024-02-22 14:13:36',	NULL),
////            (25, 2,	'54228573',	'Generali Poisťovňa, pobočka poisťovne z iného členského št.',	'GENERALI',	'Lamačská cesta 3/A',	'Bratislava',	'84104',	NULL,	NULL,	'2024-02-22 14:19:36',	'2024-02-22 14:19:36',	NULL),
//            (26, 2,	'50659669',	'PREMIUM Poisťovňa, pobočka poisťovne z iného členského štátu',	'PREMIUM',	'Námestie Mateja Korvína 1',	'Bratislava',	'81107',	NULL,	NULL,	'2024-02-22 14:20:37',	'2024-02-22 14:20:37',	NULL),
//            (28, 2,	'31595545',	'KOMUNÁLNA poisťovňa, a.s. Vienna Insurance Group',	'KOMUNÁLNA',	'Štefánikova 17',	'Bratislava',	'81105',	NULL,	NULL,	'2024-02-22 14:22:11',	'2024-02-22 14:22:11',	NULL),
//            (29, 2,	'00585441',	'KOOPERATIVA poisťovňa, a.s. Vienna Insurance Group',	'KOOP',	'Štefanovičova 4',	'Bratislava',	'81623',	NULL,	NULL,	'2024-02-22 14:22:37',	'2024-02-22 14:22:37',	NULL),
//            (34, 2,	'35897741',	'AXA ASSISTANCE CZ, s.r.o., organizačná zložka BRATISLAVA',	'AXA ASSIST',	'Zámocká 30',	'Bratislava',	'81101',	NULL,	NULL,	'2024-02-22 14:27:51',	'2024-02-22 14:27:51',	NULL),
//            (36, 2,	'36816175',	'DEFEND INSURANCE s.r.o.',	'DEF',	'Pribinová 4',	'Bratislava',	'81109',	NULL,	NULL,	'2024-02-22 14:28:43',	'2024-02-22 14:28:43',	NULL),
//            (37, 2,	'35691999',	'NN Životná poisťovňa, a.s.',	'NN',	'Jesenského 4/C',	'Bratislava',	'81102',	'0850123303',	'podpora@nn.sk',	'2024-02-22 14:29:40',	'2024-02-22 14:29:40',	NULL),
//
//
//            (24, 3,	'31335004',	'Prvá stavebná sporiteľňa, a. s.',	'PSS',	'Bajkalská 30',	'Bratislava',	'82948',	NULL,	NULL,	'2024-02-22 14:19:06',	'2024-02-22 14:19:06',	NULL),
//
//
//            (11, 4,	'26442671',	'Conseq Investment Management, a.s.',	'CONSEQ',	'Burzovní palác, Rybná 682/14',	'Praha',	'11000',	NULL,	NULL,	'2024-02-22 14:12:42',	'2024-02-22 14:12:42',	NULL),
//            (21, 4,	'36864633',	'European Investment Centre, o.c.p., a. s.',	'EIC',	'Vajnorská 100/B',	'Bratislava',	'83104',	NULL,	NULL,	'2024-02-22 14:16:59',	'2024-02-22 14:16:59',	NULL),
//            (23, 4,	'51306727',	'Finax, o.c.p., a. s.',	'FINAX',	'Bajkalská 19B',	'Bratislava',	'82101',	NULL,	NULL,	'2024-02-22 14:18:04',	'2024-02-22 14:18:04',	NULL),
//            (27, 4,	'17330254',	'IAD Investments, správ. spol., a. s.',	'IAD',	'Malý trh 2/A',	'Bratislava',	'81108',	NULL,	NULL,	'2024-02-22 14:21:14',	'2024-02-22 14:21:14',	NULL),
//            (32, 4,	'64579018',	'UNIQA investiční společnosť, a.s., oragnizačná zložka SK',	'UNIQA inv.',	'Krasovského 3986/15',	'Bratislava',	'85101',	NULL,	NULL,	'2024-02-22 14:24:40',	'2024-02-22 14:24:40',	NULL),
//
//
//            (8, 5,	'35903821',	'UNIQA d.s.s., a.s.',	'UNIQA DSS',	'Krasovského 3986/15',	'Bratislava',	'85101',	NULL,	NULL,	'2024-02-22 14:11:18',	'2024-02-22 14:11:18',	NULL),
////            (33, 5,	'35901624',	'Allianz - Slovenská dôchodková správcovská spoločnosť, a.s.',	'ALL_DSS',	'Pribinova 19',	'Bratislava',	'81109',	NULL,	NULL,	'2024-02-22 14:27:05',	'2024-02-22 14:27:05',	NULL),
////            (35, 5,	'35903058', 'VÚB Generali dôchodková správcovská spoločnosť, a.s.',	'VÚB Gener',	'Mlynské nivy 1',	'Bratislava',	'82004',	NULL,	NULL,	'2024-02-22 14:28:20',	'2024-02-22 14:28:20',	NULL),
//            (39, 5,	'35902981',	'NN dôchodková správcovská spoločnosť, a.s.',	'NN_SDS',	'Jesenského 4/C',	'Bratislava',	'81102',	'0850123303',	'podpora@nn.sk',	'2024-02-22 14:30:30',	'2024-02-22 14:30:30',	NULL),
//
//
//            (9, 6,	'35977540',	'UNIQA d.d.s., a.s',	'UNIQA DDS',	'Krasovského 3986/15',	'Bratislava',	'85101',	NULL,	NULL,	'2024-02-22 14:11:39',	'2024-02-22 14:31:52',	NULL),
//            (20, 6,	'36291111',	'Doplnková dôchodková spoločnosť Tatra banky, a.s.',	'DDS TB',	'Hodžovo námestie 3',	'Bratislava',	'81106',	NULL,	NULL,	'2024-02-22 14:16:36',	'2024-02-22 14:16:36',	NULL),
//            (38, 6,	'35976853',	'NN Tatry - Sympatia, d.d.s., a.s.',	'NN_DDS',	'Jesenského 4/C',	'Bratislava',	'81102',	'0850123303',	'podpora@nn.sk',	'2024-02-22 14:29:57',	'2024-02-22 14:29:57',	NULL),
//
//
//            (16, 7,	'35942436',	'DÔVERA zdravotná poisťovňa, a. s.',	'DÔVERA',	'Einsteinova 25',	'Bratislava',	'85101',	NULL,	NULL,	'2024-02-22 14:15:08',	'2024-02-22 14:15:08',	NULL),
//
//
//            (15, 8,	'35704713',	'ČSOB Leasing, a.s.',	'ČSOB LEAS',	'Žižkova 11',	'Bratislava',	'81510',	NULL,	NULL,	'2024-02-22 14:14:34',	'2024-02-22 14:14:34',	NULL),
//            (19, 8,	'31326552',	'Tatra-Leasing, s.r.o.',	'Tatra LEAS',	'Hodžovo námestie 3',	'Bratislava',	'81106',	NULL,	NULL,	'2024-02-22 14:16:20',	'2024-02-22 14:16:20',	NULL),
//            (40, 8,	'31644333',	'BKS-Leasing s. r. o.',	'BKS leas',	'Pribinova 4',	'Bratislava',	'81109',	NULL,	NULL,	'2024-02-22 14:31:03',	'2024-02-22 14:31:03',	NULL),
//
//
//            (17, NULL,	'52814220',	'Euler Hermes Group SAS organizačná zložka',	'EULER',	'Bajkalská 19B',	'Bratislava',	'82101',	NULL,	NULL,	'2024-02-22 14:15:31',	'2024-02-22 14:15:31',	NULL),
//            (31, NULL,	'50617168',	'Atradius Credito y Caucion S.A. de Seguros y Reaseguros',	'Atradius',	'Rajská 7',	'Bratislava',	'81108',	NULL,	NULL,	'2024-02-22 14:24:15',	'2024-02-22 14:24:15',	NULL);");
    }
}

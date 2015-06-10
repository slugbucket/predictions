DROP TABLE IF EXISTS timezones;
CREATE TABLE timezones (
  id TINYINT NOT NULL PRIMARY KEY,
  tz_name CHAR(64) NOT NULL UNIQUE
) ENGINE=InnoDB COMMENT='List of available timezones';

INSERT INTO timezones(id, tz_name) VALUES
('1', ''),


('1', 'Africa/Abidjan
('2', 'Africa/Accra
('3', 'Africa/Addis_Ababa
('4', 'Africa/Algiers
('5', 'Africa/Asmara
('1', 'Africa/Asmera
('6', 'Africa/Bamako
('7', 'Africa/Bangui
('8', 'Africa/Banjul
('9', 'Africa/Bissau
('10', 'Africa/Blantyre
('11', 'Africa/Brazzaville
('12', 'Africa/Bujumbura
('13', 'Africa/Cairo
('14', 'Africa/Casablanca
('15', 'Africa/Ceuta
('16', 'Africa/Conakry
('17', 'Africa/Dakar
('18', 'Africa/Dar_es_Salaam
('19', 'Africa/Djibouti
('21', 'Africa/Douala
('22', 'Africa/El_Aaiun
('23', 'Africa/Freetown
('24', 'Africa/Gaborone
('25', 'Africa/Harare
('26', 'Africa/Johannesburg
('27', 'Africa/Kampala
('28', 'Africa/Khartoum
('29', 'Africa/Kigali
('30', 'Africa/Kinshasa
('30', 'Africa/Lagos
('30', 'Africa/Libreville
('30', 'Africa/Lome
('30', 'Africa/Luanda
('30', 'Africa/Lubumbashi
('30', 'Africa/Lusaka
('30', 'Africa/Malabo
('30', 'Africa/Maputo
('30', 'Africa/Maseru
('40', 'Africa/Mbabane
('40', 'Africa/Mogadishu
('40', 'Africa/Monrovia
('40', 'Africa/Nairobi
('40', 'Africa/Ndjamena
('40', 'Africa/Niamey
('40', 'Africa/Nouakchott
('40', 'Africa/Ouagadougou
('40', 'Africa/Porto-Novo
('40', 'Africa/Sao_Tome
('40', 'Africa/Timbuktu
Africa/Tripoli
Africa/Tunis
Africa/Windhoek

no. 	c.c.* 	coordinates* 	TZ* 	comments* 	Standard time 	Summer time 	Notes
1 	AD 	+4230+00131 	Europe/Andorra 		mUTC+01 	nUTC+02 	
2 	AE 	+2518+05518 	Asia/Dubai 		pUTC+04 	- 	
3 	AF 	+3431+06912 	Asia/Kabul 		q-UTC+04:30 	- 	
4 	AG 	+1703−06148 	America/Antigua 		hUTC−04 	- 	
5 	AI 	+1812−06304 	America/Anguilla 		hUTC−04 	- 	
6 	AL 	+4120+01950 	Europe/Tirane 		mUTC+01 	nUTC+02 	
7 	AM 	+4011+04430 	Asia/Yerevan 		pUTC+04 	qUTC+05 	
8 	AN 	+1211−06900 	America/Curacao 		hUTC−04 	- 	
9 	AO 	−0848+01314 	Africa/Luanda 		mUTC+01 	- 	
10 	AQ 	−7750+16636 	Antarctica/McMurdo 	McMurdo Station, Ross Island 	xUTC+12 	yUTC+13 	
11 	AQ 	−9000+00000 	Antarctica/South_Pole 	Amundsen-Scott Station, South Pole 	xUTC+12 	yUTC+13 	
12 	AQ 	−6734−06808 	Antarctica/Rothera 	Rothera Station, Adelaide Island 	iUTC−03 	- 	
13 	AQ 	−6448−06406 	Antarctica/Palmer 	Palmer Station, Anvers Island 	hUTC−04 	iUTC−03 	
14 	AQ 	−6736+06253 	Antarctica/Mawson 	Mawson Station, Holme Bay 	rUTC+06 	- 	
15 	AQ 	−6835+07758 	Antarctica/Davis 	Davis Station, Vestfold Hills 	sUTC+07 	- 	
16 	AQ 	−6617+11031 	Antarctica/Casey 	Casey Station, Bailey Peninsula 	tUTC+08 	- 	
17 	AQ 	−7824+10654 	Antarctica/Vostok 	Vostok Station, S Magnetic Pole 	lUTC+00 	- 	
18 	AQ 	−6640+14001 	Antarctica/DumontDUrville 	Dumont-d'Urville Station, Terre Adelie 	vUTC+10 	- 	
19 	AQ 	-690022+0393524 	Antarctica/Syowa 	Syowa Station, E Ongul I 	oUTC+03 	- 	
20 	AR 	−3436−05827 	America/Argentina/Buenos_Aires 	Buenos Aires (BA, CF) 	iUTC−03 	jUTC−02 	
21 	AR 	−3124−06411 	America/Argentina/Cordoba 	most locations (CB, CC, CN, ER, FM, MN, SE, SF) 	iUTC−03 	jUTC−02 	
22 	AR 	−2447−06525 	America/Argentina/Salta 	(SA, LP, NQ, RN) 	iUTC−03 	- 	
23 	AR 	−2411−06518 	America/Argentina/Jujuy 	Jujuy (JY) 	iUTC−03 	- 	
24 	AR 	−2649−06513 	America/Argentina/Tucuman 	Tucuman (TM) 	iUTC−03 	jUTC−02 	
25 	AR 	−2828−06547 	America/Argentina/Catamarca 	Catamarca (CT), Chubut (CH) 	iUTC−03 	- 	
26 	AR 	−2926−06651 	America/Argentina/La_Rioja 	La Rioja (LR) 	iUTC−03 	- 	
27 	AR 	−3132−06831 	America/Argentina/San_Juan 	San Juan (SJ) 	iUTC−03 	- 	
28 	AR 	−3253−06849 	America/Argentina/Mendoza 	Mendoza (MZ) 	iUTC−03 	- 	
29 	AR 	−3319−06621 	America/Argentina/San_Luis 	San Luis (SL) 	hUTC−04 	iUTC−03 	
30 	AR 	−5138−06913 	America/Argentina/Rio_Gallegos 	Santa Cruz (SC) 	iUTC−03 	- 	
31 	AR 	−5448−06818 	America/Argentina/Ushuaia 	Tierra del Fuego (TF) 	iUTC−03 	- 	
32 	AS 	−1416−17042 	Pacific/Pago_Pago 		aUTC−11 	- 	
33 	AT 	+4813+01620 	Europe/Vienna 		mUTC+01 	nUTC+02 	
34 	AU 	−3133+15905 	Australia/Lord_Howe 	Lord Howe Island 	w-UTC+10:30 	wUTC+11 	
35 	AU 	−4253+14719 	Australia/Hobart 	Tasmania - most locations 	vUTC+10 	wUTC+11 	
36 	AU 	−3956+14352 	Australia/Currie 	Tasmania - King Island 	vUTC+10 	wUTC+11 	
37 	AU 	−3749+14458 	Australia/Melbourne 	Victoria 	vUTC+10 	wUTC+11 	
38 	AU 	−3352+15113 	Australia/Sydney 	New South Wales - most locations 	vUTC+10 	wUTC+11 	
39 	AU 	−3157+14127 	Australia/Broken_Hill 	New South Wales - Yancowinna 	v-UTC+09:30 	w-UTC+10:30 	
40 	AU 	−2728+15302 	Australia/Brisbane 	Queensland - most locations 	vUTC+10 	- 	
41 	AU 	−2016+14900 	Australia/Lindeman 	Queensland - Holiday Islands 	vUTC+10 	- 	
42 	AU 	−3455+13835 	Australia/Adelaide 	South Australia 	v-UTC+09:30 	w-UTC+10:30 	
43 	AU 	−1228+13050 	Australia/Darwin 	Northern Territory 	v-UTC+09:30 	- 	
44 	AU 	−3157+11551 	Australia/Perth 	Western Australia - most locations 	tUTC+08 	- 	
45 	AU 	−3143+12852 	Australia/Eucla 	Western Australia - Eucla area 	u/UTC+08:45 	v/UTC+09:45 	
46 	AW 	+1230−06958 	America/Aruba 		hUTC−04 	- 	
47 	AX 	+6006+01957 	Europe/Mariehamn 		nUTC+02 	oUTC+03 	
48 	AZ 	+4023+04951 	Asia/Baku 		pUTC+04 	qUTC+05 	
49 	BA 	+4352+01825 	Europe/Sarajevo 		mUTC+01 	nUTC+02 	
50 	BB 	+1306−05937 	America/Barbados 		hUTC−04 	- 	
51 	BD 	+2343+09025 	Asia/Dhaka 		rUTC+06 	- 	
52 	BE 	+5050+00420 	Europe/Brussels 		mUTC+01 	nUTC+02 	
53 	BF 	+1222−00131 	Africa/Ouagadougou 		lUTC+00 	- 	
54 	BG 	+4241+02319 	Europe/Sofia 		nUTC+02 	oUTC+03 	
55 	BH 	+2623+05035 	Asia/Bahrain 		oUTC+03 	- 	
56 	BI 	−0323+02922 	Africa/Bujumbura 		nUTC+02 	- 	
57 	BJ 	+0629+00237 	Africa/Porto-Novo 		mUTC+01 	- 	
58 	BL 	+1753−06251 	America/St_Barthelemy 		hUTC−04 	- 	
59 	BM 	+3217−06446 	Atlantic/Bermuda 		hUTC−04 	iUTC−03
60 	BN 	+0456+11455 	Asia/Brunei 		tUTC+08 	- 	
61 	BO 	−1630−06809 	America/La_Paz 		hUTC−04 	- 	
62 	BR 	−0351−03225 	America/Noronha 	Atlantic islands 	jUTC−02 	- 	
63 	BR 	−0127−04829 	America/Belem 	Amapa, E Para 	iUTC−03 	- 	
64 	BR 	−0343−03830 	America/Fortaleza 	NE Brazil (MA, PI, CE, RN, PB) 	iUTC−03 	- 	
65 	BR 	−0803−03454 	America/Recife 	Pernambuco 	iUTC−03 	- 	
66 	BR 	−0712−04812 	America/Araguaina 	Tocantins 	iUTC−03 	- 	
67 	BR 	−0940−03543 	America/Maceio 	Alagoas, Sergipe 	iUTC−03 	- 	
68 	BR 	−1259−03831 	America/Bahia 	Bahia 	iUTC−03 	- 	
69 	BR 	−2332−04637 	America/Sao_Paulo 	S & SE Brazil (GO, DF, MG, ES, RJ, SP, PR, SC, RS) 	iUTC−03 	jUTC−02 	
70 	BR 	−2027−05437 	America/Campo_Grande 	Mato Grosso do Sul 	hUTC−04 	iUTC−03 	
71 	BR 	−1535−05605 	America/Cuiaba 	Mato Grosso 	hUTC−04 	iUTC−03 	
72 	BR 	−0226−05452 	America/Santarem 	W Para 	iUTC−03 	- 	
73 	BR 	−0846−06354 	America/Porto_Velho 	Rondonia 	hUTC−04 	- 	
74 	BR 	+0249−06040 	America/Boa_Vista 	Roraima 	hUTC−04 	- 	
75 	BR 	−0308−06001 	America/Manaus 	E Amazonas 	hUTC−04 	- 	
76 	BR 	−0640−06952 	America/Eirunepe 	W Amazonas 	hUTC−04 	- 	
77 	BR 	−0958−06748 	America/Rio_Branco 	Acre 	hUTC−04 	- 	
78 	BS 	+2505−07721 	America/Nassau 		gUTC−05 	hUTC−04 	
79 	BT 	+2728+08939 	Asia/Thimphu 		rUTC+06 	- 	
80 	BW 	−2439+02555 	Africa/Gaborone 		nUTC+02 	- 	
81 	BY 	+5354+02734 	Europe/Minsk 		nUTC+02 	oUTC+03 	
82 	BZ 	+1730−08812 	America/Belize 		fUTC−06 	- 	
83 	CA 	+4734−05243 	America/St_Johns 	Newfoundland Time, including SE Labrador 	i-UTC−03:30 	j-UTC−02:30 	
84 	CA 	+4439−06336 	America/Halifax 	Atlantic Time - Nova Scotia (most places), PEI 	hUTC−04 	iUTC−03 	
85 	CA 	+4612−05957 	America/Glace_Bay 	Atlantic Time - Nova Scotia - places that did not observe DST 1966-1971 	hUTC−04 	iUTC−03 	
86 	CA 	+4606−06447 	America/Moncton 	Atlantic Time - New Brunswick 	hUTC−04 	iUTC−03 	
87 	CA 	+5320−06025 	America/Goose_Bay 	Atlantic Time - Labrador - most locations 	hUTC−04 	iUTC−03 	
88 	CA 	+5125−05707 	America/Blanc-Sablon 	Atlantic Standard Time - Quebec - Lower North Shore 	hUTC−04 	- 	
89 	CA 	+4531−07334 	America/Montreal 	Eastern Time - Quebec - most locations 	gUTC−05 	hUTC−04 	
90 	CA 	+4339−07923 	America/Toronto 	Eastern Time - Ontario - most locations 	gUTC−05 	hUTC−04 	
91 	CA 	+4901−08816 	America/Nipigon 	Eastern Time - Ontario & Quebec - places that did not observe DST 1967-1973 	gUTC−05 	hUTC−04 	
92 	CA 	+4823−08915 	America/Thunder_Bay 	Eastern Time - Thunder Bay, Ontario 	gUTC−05 	hUTC−04 	
93 	CA 	+6344−06828 	America/Iqaluit 	Eastern Time - east Nunavut - most locations 	gUTC−05 	hUTC−04 	
94 	CA 	+6608−06544 	America/Pangnirtung 	Eastern Time - Pangnirtung, Nunavut 	gUTC−05 	hUTC−04 	
95 	CA 	+744144-0944945 	America/Resolute 	Eastern Standard Time - Resolute, Nunavut 	gUTC−05 	hUTC−04 	
96 	CA 	+484531-0913718 	America/Atikokan 	Eastern Standard Time - Atikokan, Ontario and Southampton I, Nunavut 	gUTC−05 	- 	
97 	CA 	+624900-0920459 	America/Rankin_Inlet 	Central Time - central Nunavut 	fUTC−06 	gUTC−05 	
98 	CA 	+4953−09709 	America/Winnipeg 	Central Time - Manitoba & west Ontario 	fUTC−06 	gUTC−05 	
99 	CA 	+4843−09434 	America/Rainy_River 	Central Time - Rainy River & Fort Frances, Ontario 	fUTC−06 	gUTC−05 	
100 	CA 	+5024−10439 	America/Regina 	Central Standard Time - Saskatchewan - most locations 	fUTC−06 	- 	
101 	CA 	+5017−10750 	America/Swift_Current 	Central Standard Time - Saskatchewan - midwest 	fUTC−06 	- 	
102 	CA 	+5333−11328 	America/Edmonton 	Mountain Time - Alberta, east British Columbia & west Saskatchewan 	eUTC−07 	fUTC−06 	
103 	CA 	+690650-1050310 	America/Cambridge_Bay 	Mountain Time - west Nunavut 	eUTC−07 	fUTC−06 	
104 	CA 	+6227−11421 	America/Yellowknife 	Mountain Time - central Northwest Territories 	eUTC−07 	fUTC−06 	
105 	CA 	+682059-1334300 	America/Inuvik 	Mountain Time - west Northwest Territories 	eUTC−07 	fUTC−06 	
106 	CA 	+5946−12014 	America/Dawson_Creek 	Mountain Standard Time - Dawson Creek & Fort Saint John, British Columbia 	eUTC−07 	- 	
107 	CA 	+4916−12307 	America/Vancouver 	Pacific Time - west British Columbia 	dUTC−08 	eUTC−07 	
108 	CA 	+6043−13503 	America/Whitehorse 	Pacific Time - south Yukon 	dUTC−08 	eUTC−07 	
109 	CA 	+6404−13925 	America/Dawson 	Pacific Time - north Yukon 	dUTC−08 	eUTC−07 	
110 	CC 	−1210+09655 	Indian/Cocos 		s-UTC+06:30 	- 	
111 	CD 	−0418+01518 	Africa/Kinshasa 	west Dem. Rep. of Congo 	mUTC+01 	- 	
112 	CD 	−1140+02728 	Africa/Lubumbashi 	east Dem. Rep. of Congo 	nUTC+02 	- 	
113 	CF 	+0422+01835 	Africa/Bangui 		mUTC+01 	- 	
114 	CG 	−0416+01517 	Africa/Brazzaville 		mUTC+01 	- 	
115 	CH 	+4723+00832 	Europe/Zurich 		mUTC+01 	nUTC+02 	
116 	CI 	+0519−00402 	Africa/Abidjan 		lUTC+00 	- 	
117 	CK 	−2114−15946 	Pacific/Rarotonga 		bUTC−10 	- 	
118 	CL 	−3327−07040 	America/Santiago 	most locations 	hUTC−04 	iUTC−03 	
119 	CL 	−2709−10926 	Pacific/Easter 	Easter Island & Sala y Gomez 	fUTC−06 	gUTC−05 	
120 	CM 	+0403+00942 	Africa/Douala 		mUTC+01 	- 	
121 	CN 	+3114+12128 	Asia/Shanghai 	east China - Beijing, Guangdong, Shanghai, etc. 	tUTC+08 	- 	Covering historic Chungyuan time zone.
122 	CN 	+4545+12641 	Asia/Harbin 	Heilongjiang (except Mohe), Jilin 	tUTC+08 	- 	Covering historic Changpai time zone.
123 	CN 	+2934+10635 	Asia/Chongqing 	central China - Sichuan, Yunnan, Guangxi, Shaanxi, Guizhou, etc. 	tUTC+08 	- 	Covering historic Kansu-Szechuan time zone.
124 	CN 	+4348+08735 	Asia/Urumqi 	most of Tibet & Xinjiang 	tUTC+08 	- 	Covering historic Sinkiang-Tibet time zone.
125 	CN 	+3929+07559 	Asia/Kashgar 	west Tibet & Xinjiang 	tUTC+08 	- 	Covering historic Kunlun time zone.
126 	CO 	+0436−07405 	America/Bogota 		gUTC−05 	- 	
127 	CR 	+0956−08405 	America/Costa_Rica 		fUTC−06 	- 	
128 	CU 	+2308−08222 	America/Havana 		gUTC−05 	hUTC−04 	
129 	CV 	+1455−02331 	Atlantic/Cape_Verde 		kUTC−01 	- 	
130 	CX 	−1025+10543 	Indian/Christmas 		sUTC+07 	- 	
131 	CY 	+3510+03322 	Asia/Nicosia 		nUTC+02 	oUTC+03 	
132 	CZ 	+5005+01426 	Europe/Prague 		mUTC+01 	nUTC+02 	
133 	DE 	+5230+01322 	Europe/Berlin 		mUTC+01 	nUTC+02 	In 1945, the Trizone did not follow Berlin's switch to DST, see Time in Germany
134 	DJ 	+1136+04309 	Africa/Djibouti 		oUTC+03 	- 	
135 	DK 	+5540+01235 	Europe/Copenhagen 		mUTC+01 	nUTC+02 	
136 	DM 	+1518−06124 	America/Dominica 		hUTC−04 	- 	
137 	DO 	+1828−06954 	America/Santo_Domingo 		hUTC−04 	- 	
138 	DZ 	+3647+00303 	Africa/Algiers 		mUTC+01 	- 	
139 	EC 	−0210−07950 	America/Guayaquil 	mainland 	gUTC−05 	- 	
140 	EC 	−0054−08936 	Pacific/Galapagos 	Galapagos Islands 	fUTC−06 	- 	
141 	EE 	+5925+02445 	Europe/Tallinn 		nUTC+02 	oUTC+03 	
142 	EG 	+3003+03115 	Africa/Cairo 		nUTC+02 	oUTC+03 	
143 	EH 	+2709−01312 	Africa/El_Aaiun 		lUTC+00 	- 	
144 	ER 	+1520+03853 	Africa/Asmara 		oUTC+03 	- 	
145 	ES 	+4024−00341 	Europe/Madrid 	mainland 	mUTC+01 	nUTC+02 	
146 	ES 	+3553−00519 	Africa/Ceuta 	Ceuta & Melilla 	mUTC+01 	nUTC+02 	
147 	ES 	+2806−01524 	Atlantic/Canary 	Canary Islands 	lUTC+00 	mUTC+01 	
148 	ET 	+0902+03842 	Africa/Addis_Ababa 		oUTC+03 	- 	
149 	FI 	+6010+02458 	Europe/Helsinki 		nUTC+02 	oUTC+03 	
150 	FJ 	−1808+17825 	Pacific/Fiji 		xUTC+12 	yUTC+13 	
151 	FK 	−5142−05751 	Atlantic/Stanley 		hUTC−04 	iUTC−03 	
152 	FM 	+0725+15147 	Pacific/Truk 	Truk (Chuuk) and Yap 	vUTC+10 	- 	
153 	FM 	+0658+15813 	Pacific/Ponape 	Ponape (Pohnpei) 	wUTC+11 	- 	
154 	FM 	+0519+16259 	Pacific/Kosrae 	Kosrae 	wUTC+11 	- 	
155 	FO 	+6201−00646 	Atlantic/Faroe 		lUTC+00 	mUTC+01 	
156 	FR 	+4852+00220 	Europe/Paris 		mUTC+01 	nUTC+02 	
157 	GA 	+0023+00927 	Africa/Libreville 		mUTC+01 	- 	
158 	GB 	+513030-0000731 	Europe/London 		lUTC+00 	mUTC+01 	
159 	GD 	+1203−06145 	America/Grenada 		hUTC−04 	- 	
160 	GE 	+4143+04449 	Asia/Tbilisi 		pUTC+04 	- 	
161 	GF 	+0456−05220 	America/Cayenne 		iUTC−03 	- 	
162 	GG 	+4927−00232 	Europe/Guernsey 		lUTC+00 	mUTC+01 	
163 	GH 	+0533−00013 	Africa/Accra 		lUTC+00 	- 	
164 	GI 	+3608−00521 	Europe/Gibraltar 		mUTC+01 	nUTC+02 	
165 	GL 	+6411−05144 	America/Godthab 	most locations 	iUTC−03 	jUTC−02 	
166 	GL 	+7646−01840 	America/Danmarkshavn 	east coast, north of Scoresbysund 	lUTC+00 	- 	
167 	GL 	+7029−02158 	America/Scoresbysund 	Scoresbysund / Ittoqqortoormiit 	kUTC−01 	lUTC+00 	
168 	GL 	+7634−06847 	America/Thule 	Thule / Pituffik 	hUTC−04 	iUTC−03 	
169 	GM 	+1328−01639 	Africa/Banjul 		lUTC+00 	- 	
170 	GN 	+0931−01343 	Africa/Conakry 		lUTC+00 	- 	
171 	GP 	+1614−06132 	America/Guadeloupe 		hUTC−04 	- 	
172 	GQ 	+0345+00847 	Africa/Malabo 		mUTC+01 	- 	
173 	GR 	+3758+02343 	Europe/Athens 		nUTC+02 	oUTC+03 	
174 	GS 	−5416−03632 	Atlantic/South_Georgia 		jUTC−02 	- 	
175 	GT 	+1438−09031 	America/Guatemala 		fUTC−06 	- 	
176 	GU 	+1328+14445 	Pacific/Guam 		vUTC+10 	- 	
177 	GW 	+1151−01535 	Africa/Bissau 		lUTC+00 	- 	
178 	GY 	+0648−05810 	America/Guyana 		hUTC−04 	- 	
179 	HK 	+2217+11409 	Asia/Hong_Kong 		tUTC+08 	- 	
180 	HN 	+1406−08713 	America/Tegucigalpa 		fUTC−06 	- 	
181 	HR 	+4548+01558 	Europe/Zagreb 		mUTC+01 	nUTC+02 	
182 	HT 	+1832−07220 	America/Port-au-Prince 		gUTC−05 	- 	
183 	HU 	+4730+01905 	Europe/Budapest 		mUTC+01 	nUTC+02 	
184 	ID 	−0610+10648 	Asia/Jakarta 	Java & Sumatra 	sUTC+07 	- 	
185 	ID 	−0002+10920 	Asia/Pontianak 	west & central Borneo 	sUTC+07 	- 	
186 	ID 	−0507+11924 	Asia/Makassar 	east & south Borneo, Celebes, Bali, Nusa Tengarra, west Timor 	tUTC+08 	- 	
187 	ID 	−0232+14042 	Asia/Jayapura 	Irian Jaya & the Moluccas 	uUTC+09 	- 	
188 	IE 	+5320−00615 	Europe/Dublin 		lUTC+00 	mUTC+01 	
189 	IL 	+3146+03514 	Asia/Jerusalem 		nUTC+02 	oUTC+03 	
190 	IM 	+5409−00428 	Europe/Isle_of_Man 		lUTC+00 	mUTC+01 	
191 	IN 	+2232+08822 	Asia/Kolkata 		r-UTC+05:30 	- 	Note: Different zones in history, see Time in India
192 	IO 	−0720+07225 	Indian/Chagos 		rUTC+06 	- 	
193 	IQ 	+3321+04425 	Asia/Baghdad 		oUTC+03 	- 	
194 	IR 	+3540+05126 	Asia/Tehran 		p-UTC+03:30 	q-UTC+04:30 	
195 	IS 	+6409−02151 	Atlantic/Reykjavik 		lUTC+00 	- 	
196 	IT 	+4154+01229 	Europe/Rome 		mUTC+01 	nUTC+02 	
197 	JE 	+4912−00207 	Europe/Jersey 		lUTC+00 	mUTC+01 	
198 	JM 	+1800−07648 	America/Jamaica 		gUTC−05 	- 	
199 	JO 	+3157+03556 	Asia/Amman 		nUTC+02 	oUTC+03 	
200 	JP 	+353916+1394441 	Asia/Tokyo 		uUTC+09 	- 	
201 	KE 	−0117+03649 	Africa/Nairobi 		oUTC+03 	- 	
202 	KG 	+4254+07436 	Asia/Bishkek 		rUTC+06 	- 	
203 	KH 	+1133+10455 	Asia/Phnom_Penh 		sUTC+07 	- 	
204 	KI 	+0125+17300 	Pacific/Tarawa 	Gilbert Islands 	xUTC+12 	- 	
205 	KI 	−0308−17105 	Pacific/Enderbury 	Phoenix Islands 	yUTC+13 	- 	
206 	KI 	+0152−15720 	Pacific/Kiritimati 	Line Islands 	zUTC+14 	- 	
207 	KM 	−1141+04316 	Indian/Comoro 		oUTC+03 	- 	
208 	KN 	+1718−06243 	America/St_Kitts 		hUTC−04 	- 	
209 	KP 	+3901+12545 	Asia/Pyongyang 		uUTC+09 	- 	
210 	KR 	+3733+12658 	Asia/Seoul 		uUTC+09 	- 	
211 	KW 	+2920+04759 	Asia/Kuwait 		oUTC+03 	- 	
212 	KY 	+1918−08123 	America/Cayman 		gUTC−05 	- 	
213 	KZ 	+4315+07657 	Asia/Almaty 	most locations 	rUTC+06 	- 	
214 	KZ 	+4448+06528 	Asia/Qyzylorda 	Qyzylorda (Kyzylorda, Kzyl-Orda) 	rUTC+06 	- 	
215 	KZ 	+5017+05710 	Asia/Aqtobe 	Aqtobe (Aktobe) 	qUTC+05 	- 	
216 	KZ 	+4431+05016 	Asia/Aqtau 	Atyrau (Atirau, Gur'yev), Mangghystau (Mankistau) 	qUTC+05 	- 	
217 	KZ 	+5113+05121 	Asia/Oral 	West Kazakhstan 	qUTC+05 	- 	
218 	LA 	+1758+10236 	Asia/Vientiane 		sUTC+07 	- 	
219 	LB 	+3353+03530 	Asia/Beirut 		nUTC+02 	oUTC+03 	
220 	LC 	+1401−06100 	America/St_Lucia 		hUTC−04 	- 	
221 	LI 	+4709+00931 	Europe/Vaduz 		mUTC+01 	nUTC+02 	
222 	LK 	+0656+07951 	Asia/Colombo 		r-UTC+05:30 	- 	
223 	LR 	+0618−01047 	Africa/Monrovia 		lUTC+00 	- 	
224 	LS 	−2928+02730 	Africa/Maseru 		nUTC+02 	- 	
225 	LT 	+5441+02519 	Europe/Vilnius 		nUTC+02 	oUTC+03 	
226 	LU 	+4936+00609 	Europe/Luxembourg 		mUTC+01 	nUTC+02 	
227 	LV 	+5657+02406 	Europe/Riga 		nUTC+02 	oUTC+03 	
228 	LY 	+3254+01311 	Africa/Tripoli 		nUTC+02 	- 	
229 	MA 	+3339−00735 	Africa/Casablanca 		lUTC+00 	- 	
230 	MC 	+4342+00723 	Europe/Monaco 		mUTC+01 	nUTC+02 	
231 	MD 	+4700+02850 	Europe/Chisinau 		nUTC+02 	oUTC+03 	
232 	ME 	+4226+01916 	Europe/Podgorica 		mUTC+01 	nUTC+02 	
233 	MF 	+1804−06305 	America/Marigot 		hUTC−04 	- 	
234 	MG 	−1855+04731 	Indian/Antananarivo 		oUTC+03 	- 	
235 	MH 	+0709+17112 	Pacific/Majuro 	most locations 	xUTC+12 	- 	
236 	MH 	+0905+16720 	Pacific/Kwajalein 	Kwajalein 	xUTC+12 	- 	
237 	MK 	+4159+02126 	Europe/Skopje 		mUTC+01 	nUTC+02 	
238 	ML 	+1239−00800 	Africa/Bamako 		lUTC+00 	- 	
239 	MM 	+1647+09610 	Asia/Rangoon 		s-UTC+06:30 	- 	
240 	MN 	+4755+10653 	Asia/Ulaanbaatar 	most locations 	tUTC+08 	- 	
241 	MN 	+4801+09139 	Asia/Hovd 	Bayan-Olgiy, Govi-Altai, Hovd, Uvs, Zavkhan 	sUTC+07 	- 	
242 	MN 	+4804+11430 	Asia/Choibalsan 	Dornod, Sukhbaatar 	tUTC+08 	- 	
243 	MO 	+2214+11335 	Asia/Macau 		tUTC+08 	- 	
244 	MP 	+1512+14545 	Pacific/Saipan 		vUTC+10 	- 	
245 	MQ 	+1436−06105 	America/Martinique 		hUTC−04 	- 	
246 	MR 	+1806−01557 	Africa/Nouakchott 		lUTC+00 	- 	
247 	MS 	+1643−06213 	America/Montserrat 		hUTC−04 	- 	
248 	MT 	+3554+01431 	Europe/Malta 		mUTC+01 	nUTC+02 	
249 	MU 	−2010+05730 	Indian/Mauritius 		pUTC+04 	- 	
250 	MV 	+0410+07330 	Indian/Maldives 		qUTC+05 	- 	
251 	MW 	−1547+03500 	Africa/Blantyre 		nUTC+02 	- 	
252 	MX 	+1924−09909 	America/Mexico_City 	Central Time - most locations 	fUTC−06 	gUTC−05 	
253 	MX 	+2105−08646 	America/Cancun 	Central Time - Quintana Roo 	fUTC−06 	gUTC−05 	
254 	MX 	+2058−08937 	America/Merida 	Central Time - Campeche, Yucatan 	fUTC−06 	gUTC−05 	
255 	MX 	+2540−10019 	America/Monterrey 	Mexican Central Time - Coahuila, Durango, Nuevo Leon, Tamaulipas away from US border 	fUTC−06 	gUTC−05 	
256 	MX 	+2550−09730 	America/Matamoros 	US Central Time - Coahuila, Durango, Nuevo Leon, Tamaulipas near US border 	fUTC−06 	gUTC−05 	
257 	MX 	+2313−10625 	America/Mazatlan 	Mountain Time - S Baja, Nayarit, Sinaloa 	eUTC−07 	fUTC−06 	
258 	MX 	+2838−10605 	America/Chihuahua 	Mexican Mountain Time - Chihuahua away from US border 	eUTC−07 	fUTC−06 	
259 	MX 	+2934−10425 	America/Ojinaga 	US Mountain Time - Chihuahua near US border 	eUTC−07 	fUTC−06 	
260 	MX 	+2904−11058 	America/Hermosillo 	Mountain Standard Time - Sonora 	eUTC−07 	- 	
261 	MX 	+3232−11701 	America/Tijuana 	US Pacific Time - Baja California near US border 	dUTC−08 	eUTC−07 	
262 	MX 	+3018−11452 	America/Santa_Isabel 	Mexican Pacific Time - Baja California away from US border 	dUTC−08 	eUTC−07 	
263 	MY 	+0310+10142 	Asia/Kuala_Lumpur 	peninsular Malaysia 	tUTC+08 	- 	
264 	MY 	+0133+11020 	Asia/Kuching 	Sabah & Sarawak 	tUTC+08 	- 	
265 	MZ 	−2558+03235 	Africa/Maputo 		nUTC+02 	- 	
266 	NA 	−2234+01706 	Africa/Windhoek 		mUTC+01 	nUTC+02 	
267 	NC 	−2216+16627 	Pacific/Noumea 		wUTC+11 	- 	
268 	NE 	+1331+00207 	Africa/Niamey 		mUTC+01 	- 	
269 	NF 	−2903+16758 	Pacific/Norfolk 		x-UTC+11:30 	- 	
270 	NG 	+0627+00324 	Africa/Lagos 		mUTC+01 	- 	
271 	NI 	+1209−08617 	America/Managua 		fUTC−06 	- 	
272 	NL 	+5222+00454 	Europe/Amsterdam 		mUTC+01 	nUTC+02 	
273 	NO 	+5955+01045 	Europe/Oslo 		mUTC+01 	nUTC+02 	
274 	NP 	+2743+08519 	Asia/Kathmandu 		r/UTC+05:45 	- 	
275 	NR 	−0031+16655 	Pacific/Nauru 		xUTC+12 	- 	
276 	NU 	−1901−16955 	Pacific/Niue 		aUTC−11 	- 	
277 	NZ 	−3652+17446 	Pacific/Auckland 	most locations 	xUTC+12 	yUTC+13 	
278 	NZ 	−4357−17633 	Pacific/Chatham 	Chatham Islands 	y/UTC+12:45 	z/UTC+13:45 	
279 	OM 	+2336+05835 	Asia/Muscat 		pUTC+04 	- 	
280 	PA 	+0858−07932 	America/Panama 		gUTC−05 	- 	
281 	PE 	−1203−07703 	America/Lima 		gUTC−05 	- 	
282 	PF 	−1732−14934 	Pacific/Tahiti 	Society Islands 	bUTC−10 	- 	
283 	PF 	−0900−13930 	Pacific/Marquesas 	Marquesas Islands 	c-UTC−09:30 	- 	
284 	PF 	−2308−13457 	Pacific/Gambier 	Gambier Islands 	cUTC−09 	- 	
285 	PG 	−0930+14710 	Pacific/Port_Moresby 		vUTC+10 	- 	
286 	PH 	+1435+12100 	Asia/Manila 		tUTC+08 	- 	
287 	PK 	+2452+06703 	Asia/Karachi 		rUTC+06 	- 	
288 	PL 	+5215+02100 	Europe/Warsaw 		mUTC+01 	nUTC+02 	
289 	PM 	+4703−05620 	America/Miquelon 		iUTC−03 	jUTC−02 	
290 	PN 	−2504−13005 	Pacific/Pitcairn 		dUTC−08 	- 	
291 	PR 	+182806-0660622 	America/Puerto_Rico 		hUTC−04 	- 	
292 	PS 	+3130+03428 	Asia/Gaza 		nUTC+02 	oUTC+03 	
293 	PT 	+3843−00908 	Europe/Lisbon 	mainland 	lUTC+00 	mUTC+01 	
294 	PT 	+3238−01654 	Atlantic/Madeira 	Madeira Islands 	lUTC+00 	mUTC+01 	
295 	PT 	+3744−02540 	Atlantic/Azores 	Azores 	kUTC−01 	lUTC+00 	
296 	PW 	+0720+13429 	Pacific/Palau 		uUTC+09 	- 	
297 	PY 	−2516−05740 	America/Asuncion 		hUTC−04 	iUTC−03 	
298 	QA 	+2517+05132 	Asia/Qatar 		oUTC+03 	- 	
299 	RE 	−2052+05528 	Indian/Reunion 		pUTC+04 	- 	
300 	RO 	+4426+02606 	Europe/Bucharest 		nUTC+02 	oUTC+03 	
301 	RS 	+4450+02030 	Europe/Belgrade 		mUTC+01 	nUTC+02 	
302 	RU 	+5443+02030 	Europe/Kaliningrad 	Moscow-01 - Kaliningrad 	nUTC+02 	oUTC+03 	
303 	RU 	+5545+03735 	Europe/Moscow 	Moscow+00 - west Russia 	oUTC+03 	pUTC+04 	
304 	RU 	+4844+04425 	Europe/Volgograd 	Moscow+00 - Caspian Sea 	oUTC+03 	pUTC+04 	
305 	RU 	+5312+05009 	Europe/Samara 	Moscow+00 - Samara, Udmurtia 	oUTC+03 	pUTC+04 	
306 	RU 	+5651+06036 	Asia/Yekaterinburg 	Moscow+02 - Urals 	qUTC+05 	rUTC+06 	
307 	RU 	+5500+07324 	Asia/Omsk 	Moscow+03 - west Siberia 	rUTC+06 	sUTC+07 	
308 	RU 	+5502+08255 	Asia/Novosibirsk 	Moscow+03 - Novosibirsk 	rUTC+06 	sUTC+07 	
309 	RU 	+5345+08707 	Asia/Novokuznetsk 	Moscow+03 - Novokuznetsk 	rUTC+06 	sUTC+07 	
310 	RU 	+5601+09250 	Asia/Krasnoyarsk 	Moscow+04 - Yenisei River 	sUTC+07 	tUTC+08 	
311 	RU 	+5216+10420 	Asia/Irkutsk 	Moscow+05 - Lake Baikal 	tUTC+08 	uUTC+09 	
312 	RU 	+6200+12940 	Asia/Yakutsk 	Moscow+06 - Lena River 	uUTC+09 	vUTC+10 	
313 	RU 	+4310+13156 	Asia/Vladivostok 	Moscow+07 - Amur River 	vUTC+10 	wUTC+11 	
314 	RU 	+4658+14242 	Asia/Sakhalin 	Moscow+07 - Sakhalin Island 	vUTC+10 	wUTC+11 	
315 	RU 	+5934+15048 	Asia/Magadan 	Moscow+08 - Magadan 	wUTC+11 	xUTC+12 	
316 	RU 	+5301+15839 	Asia/Kamchatka 	Moscow+08 - Kamchatka 	xUTC+11 	yUTC+12 	
317 	RU 	+6445+17729 	Asia/Anadyr 	Moscow+08 - Bering Sea 	xUTC+11 	yUTC+12 	
318 	RW 	−0157+03004 	Africa/Kigali 		nUTC+02 	- 	
319 	SA 	+2438+04643 	Asia/Riyadh 		oUTC+03 	- 	
320 	SB 	−0932+16012 	Pacific/Guadalcanal 		wUTC+11 	- 	
321 	SC 	−0440+05528 	Indian/Mahe 		pUTC+04 	- 	
322 	SD 	+1536+03232 	Africa/Khartoum 		oUTC+03 	- 	
323 	SE 	+5920+01803 	Europe/Stockholm 		mUTC+01 	nUTC+02 	
324 	SG 	+0117+10351 	Asia/Singapore 		tUTC+08 	- 	
325 	SH 	−1555−00542 	Atlantic/St_Helena 	Ascension and Tristan da Cunha 	lUTC+00 	- 	
326 	SI 	+4603+01431 	Europe/Ljubljana 		mUTC+01 	nUTC+02 	
327 	SJ 	+7800+01600 	Arctic/Longyearbyen 		mUTC+01 	nUTC+02 	
328 	SK 	+4809+01707 	Europe/Bratislava 		mUTC+01 	nUTC+02 	
329 	SL 	+0830−01315 	Africa/Freetown 		lUTC+00 	- 	
330 	SM 	+4355+01228 	Europe/San_Marino 		mUTC+01 	nUTC+02 	
331 	SN 	+1440−01726 	Africa/Dakar 		lUTC+00 	- 	
332 	SO 	+0204+04522 	Africa/Mogadishu 		oUTC+03 	- 	
333 	SR 	+0550−05510 	America/Paramaribo 		iUTC−03 	- 	
334 	ST 	+0020+00644 	Africa/Sao_Tome 		lUTC+00 	- 	
335 	SV 	+1342−08912 	America/El_Salvador 		fUTC−06 	- 	
336 	SY 	+3330+03618 	Asia/Damascus 		nUTC+02 	oUTC+03 	
337 	SZ 	−2618+03106 	Africa/Mbabane 		nUTC+02 	- 	
338 	TC 	+2128−07108 	America/Grand_Turk 		gUTC−05 	hUTC−04 	
339 	TD 	+1207+01503 	Africa/Ndjamena 		mUTC+01 	- 	
340 	TF 	-492110+0701303 	Indian/Kerguelen 		qUTC+05 	- 	
341 	TG 	+0608+00113 	Africa/Lome 		lUTC+00 	- 	
342 	TH 	+1345+10031 	Asia/Bangkok 		sUTC+07 	- 	
343 	TJ 	+3835+06848 	Asia/Dushanbe 		qUTC+05 	- 	
344 	TK 	−0922−17114 	Pacific/Fakaofo 		bUTC−10 	- 	
345 	TL 	−0833+12535 	Asia/Dili 		uUTC+09 	- 	
346 	TM 	+3757+05823 	Asia/Ashgabat 		qUTC+05 	- 	
347 	TN 	+3648+01011 	Africa/Tunis 		mUTC+01 	nUTC+02 	
348 	TO 	−2110−17510 	Pacific/Tongatapu 		yUTC+13 	- 	
349 	TR 	+4101+02858 	Europe/Istanbul 		nUTC+02 	oUTC+03 	
350 	TT 	+1039−06131 	America/Port_of_Spain 		hUTC−04 	- 	
351 	TV 	−0831+17913 	Pacific/Funafuti 		xUTC+12 	- 	
352 	TW 	+2503+12130 	Asia/Taipei 		tUTC+08 	- 	
353 	TZ 	−0648+03917 	Africa/Dar_es_Salaam 		oUTC+03 	- 	
354 	UA 	+5026+03031 	Europe/Kiev 	most locations 	nUTC+02 	oUTC+03 	
355 	UA 	+4837+02218 	Europe/Uzhgorod 	Ruthenia 	nUTC+02 	oUTC+03 	
356 	UA 	+4750+03510 	Europe/Zaporozhye 	Zaporozh'ye, E Lugansk / Zaporizhia, E Luhansk 	nUTC+02 	oUTC+03 	
357 	UA 	+4457+03406 	Europe/Simferopol 	central Crimea 	nUTC+02 	oUTC+03 	
358 	UG 	+0019+03225 	Africa/Kampala 		oUTC+03 	- 	
359 	UM 	+1645−16931 	Pacific/Johnston 	Johnston Atoll 	bUTC−10 	- 	
360 	UM 	+2813−17722 	Pacific/Midway 	Midway Islands 	aUTC−11 	- 	
361 	UM 	+1917+16637 	Pacific/Wake 	Wake Island 	xUTC+12 	- 	
362 	US 	+404251-0740023 	America/New_York 	Eastern Time 	gUTC−05 	hUTC−04 	
363 	US 	+421953-0830245 	America/Detroit 	Eastern Time - Michigan - most locations 	gUTC−05 	hUTC−04 	
364 	US 	+381515-0854534 	America/Kentucky/Louisville 	Eastern Time - Kentucky - Louisville area 	gUTC−05 	hUTC−04 	
365 	US 	+364947-0845057 	America/Kentucky/Monticello 	Eastern Time - Kentucky - Wayne County 	gUTC−05 	hUTC−04 	
366 	US 	+394606-0860929 	America/Indiana/Indianapolis 	Eastern Time - Indiana - most locations 	gUTC−05 	hUTC−04 	
367 	US 	+384038-0873143 	America/Indiana/Vincennes 	Eastern Time - Indiana - Daviess, Dubois, Knox & Martin Counties 	gUTC−05 	hUTC−04 	
368 	US 	+410305-0863611 	America/Indiana/Winamac 	Eastern Time - Indiana - Pulaski County 	gUTC−05 	hUTC−04 	
369 	US 	+382232-0862041 	America/Indiana/Marengo 	Eastern Time - Indiana - Crawford County 	gUTC−05 	hUTC−04 	
370 	US 	+382931-0871643 	America/Indiana/Petersburg 	Eastern Time - Indiana - Pike County 	gUTC−05 	hUTC−04 	
371 	US 	+384452-0850402 	America/Indiana/Vevay 	Eastern Time - Indiana - Switzerland County 	gUTC−05 	hUTC−04 	
372 	US 	+415100-0873900 	America/Chicago 	Central Time 	fUTC−06 	gUTC−05 	
373 	US 	+375711-0864541 	America/Indiana/Tell_City 	Central Time - Indiana - Perry County 	fUTC−06 	gUTC−05 	
374 	US 	+411745-0863730 	America/Indiana/Knox 	Central Time - Indiana - Starke County 	fUTC−06 	gUTC−05 	
375 	US 	+450628-0873651 	America/Menominee 	Central Time - Michigan - Dickinson, Gogebic, Iron & Menominee Counties 	fUTC−06 	gUTC−05 	
376 	US 	+470659-1011757 	America/North_Dakota/Center 	Central Time - North Dakota - Oliver County 	fUTC−06 	gUTC−05 	
377 	US 	+465042-1012439 	America/North_Dakota/New_Salem 	Central Time - North Dakota - Morton County (except Mandan area) 	fUTC−06 	gUTC−05 	
378 	US 	+394421-1045903 	America/Denver 	Mountain Time 	eUTC−07 	fUTC−06 	
379 	US 	+433649-1161209 	America/Boise 	Mountain Time - south Idaho & east Oregon 	eUTC−07 	fUTC−06 	
380 	US 	+364708-1084111 	America/Shiprock 	Mountain Time - Navajo 	eUTC−07 	fUTC−06 	
381 	US 	+332654-1120424 	America/Phoenix 	Mountain Standard Time - Arizona 	eUTC−07 	- 	
382 	US 	+340308-1181434 	America/Los_Angeles 	Pacific Time 	dUTC−08 	eUTC−07 	
383 	US 	+611305-1495401 	America/Anchorage 	Alaska Time 	cUTC−09 	dUTC−08 	
384 	US 	+581807-1342511 	America/Juneau 	Alaska Time - Alaska panhandle 	cUTC−09 	dUTC−08 	
385 	US 	+593249-1394338 	America/Yakutat 	Alaska Time - Alaska panhandle neck 	cUTC−09 	dUTC−08 	
386 	US 	+643004-1652423 	America/Nome 	Alaska Time - west Alaska 	cUTC−09 	dUTC−08 	
387 	US 	+515248-1763929 	America/Adak 	Aleutian Islands 	bUTC−10 	cUTC−09 	
388 	US 	+211825-1575130 	Pacific/Honolulu 	Hawaii 	bUTC−10 	- 	
389 	UY 	−3453−05611 	America/Montevideo 		iUTC−03 	jUTC−02 	
390 	UZ 	+3940+06648 	Asia/Samarkand 	west Uzbekistan 	qUTC+05 	- 	
391 	UZ 	+4120+06918 	Asia/Tashkent 	east Uzbekistan 	qUTC+05 	- 	
392 	VA 	+415408+0122711 	Europe/Vatican 		mUTC+01 	nUTC+02 	
393 	VC 	+1309−06114 	America/St_Vincent 		hUTC−04 	- 	
394 	VE 	+1030−06656 	America/Caracas 		h-UTC−04:30 	- 	
395 	VG 	+1827−06437 	America/Tortola 		hUTC−04 	- 	
396 	VI 	+1821−06456 	America/St_Thomas 		hUTC−04 	- 	
397 	VN 	+1045+10640 	Asia/Ho_Chi_Minh 		sUTC+07 	- 	
398 	VU 	−1740+16825 	Pacific/Efate 		wUTC+11 	- 	
399 	WF 	−1318−17610 	Pacific/Wallis 		xUTC+12 	- 	
400 	WS 	−1350−17144 	Pacific/Apia 		aUTC−11 	xUTC-10 	
401 	YE 	+1245+04512 	Asia/Aden 		oUTC+03 	- 	
402 	YT 	−1247+04514 	Indian/Mayotte 		oUTC+03 	- 	
403 	ZA 	−2615+02800 	Africa/Johannesburg 		nUTC+02 	- 	
404 	ZM 	−1525+02817 	Africa/Lusaka 		nUTC+02 	- 	
405 	ZW 	−1750+03103 	Africa/Harare 		nUTC+02

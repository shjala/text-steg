<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<?php

    /* 
    Lalevoo -- A Tool for Text Steganography In Farsi Language
    Coded By Shahriyar Jalayeri
    */
    
    error_reporting(0);
    
    /* TODO : constract the Binary Encoding table dynamically */
    $ENCODING_SIZE = 6;
    
    /* if this charachters are in middle of a word, we can use keshideh charachter after them. */
    $KESHIDEH_CHARS = array("ب", "پ", "ت", "ث", "ج", "چ", "ح", "خ", "س", "ش", "ص", "ض", "ط", "ظ", "غ", "غ", "ف", "ک", "گ", "ل", "م", "ن", "ه", "ی");
    
    /* array size is 64, we use 6-bit encoding */
    $FARSI_CHARS_BIN_ENCODING = array("000000","000001","000010","000011","000100","000101","000110","000111","001000","001001","001010","001011","001100","001101","001110","001111","010000","010001","010010","010011","010100","010101","010110","010111","011000","011001","011010","011011","011100","011101","011110","011111","100000","100001","100010","100011","100100","100101","100110","100111","101000","101001","101010","101011","101100","101101","101110","101111","110000","110001","110010","110011","110100","110101","110110","110111","111000","111001","111010","111011","111100","111101","111110","111111");
    
    /* array size is 61 (room for only 3 more charachters with 6-bit encoding). We use "|" charachter as message termination symbol. */ 
    $FARSI_CHARS = array("ا", "آ", "ب","پ","ت","ث","ج","چ","ح","خ","د","ذ","ر","ز","ژ","س","ش","ص","ض","ط","ظ","ع","غ","ف","ق","ک","گ","ل","م","ن","و","ه","ی", "ي", "ئ", "1", "2","3","4","5","6","7","8","9","0", " ", ".", "،", "!", "؟", "۱", "۲", "۳", "۴", "۵", "۶", "۷", "۸", "۹", "۰", "|");
    
    $KESHIDEH = "ـ";
    $TERMINATOR = "|";



    function str_split_unicode($str, $l = 0) 
    {
        if ($l > 0) {
            $ret = array();
            $len = mb_strlen($str, "UTF-8");
            for ($i = 0; $i < $len; $i += $l) {
                $ret[] = mb_substr($str, $i, $l, "UTF-8");
            }
            return $ret;
        }
        return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
    }
    
    function is_ending_char($pos, $text)
    {
        $splited = str_split_unicode($text);
        
        if ($splited[$pos+1] == " ")
            return true;
        elseif ($splited[$pos+1] == ".")
            return true;
        elseif ($splited[$pos+1] == "،")
            return true;
        elseif ($splited[$pos+1] == "!")
            return true;
        elseif ($splited[$pos+1] == "؟")
            return true;
        
        return false;
        
    }
    
    function is_keshideh_char($char)
    {
        global $KESHIDEH_CHARS;
        
        for ($i = 0; $i < count($KESHIDEH_CHARS); $i++) {
            if ($char == $KESHIDEH_CHARS[$i])
                return true;
        }
        
        return false;
    }
    
    function get_array_pos($array, $char)
    {
        for ($i = 0; $i < count($array); $i++) {
            if ($char == $array[$i])
                return $i;
        }
        
        return false;
    }
    
    function get_text_bin($text)
    {
        global $FARSI_CHARS_BIN_ENCODING, $FARSI_CHARS;
        
        $splited = str_split_unicode($text);
        $bin_str = "";
        for ($i = 0; $i < count($splited); $i++) {
            $pos = get_array_pos($FARSI_CHARS, $splited[$i]);
            $bin_str .= $FARSI_CHARS_BIN_ENCODING[$pos];
        }
        
        return $bin_str;
    }
    
    
    function translate_bin_to_alefba($bin_text)
    {
        global $FARSI_CHARS_BIN_ENCODING, $FARSI_CHARS, $ENCODING_SIZE;
        
        $secret_str = "";
        $splited_bin = str_split($bin_text, $ENCODING_SIZE);
        
        for($i = 0; $i < count($splited_bin); $i++)
        {
            $pos = get_array_pos($FARSI_CHARS_BIN_ENCODING, $splited_bin[$i]);
            if ($pos === false || $FARSI_CHARS[$pos] == "|" || strlen($splited_bin[$i]) < $ENCODING_SIZE)
                return $secret_str;
            
            $secret_str .= $FARSI_CHARS[$pos];
        }
        
        return $secret_str;
    }
    
    function embed_text($covert_text, $secret_text)
    {
        global $KESHIDEH, $TERMINATOR;
        
        $secret_text .= $TERMINATOR;
        $secret_bin = get_text_bin($secret_text);
        $secret_bin_size = strlen($secret_bin);
        $j = 0;
        $final_str = "";
        $covert_splited = str_split_unicode($covert_text);
        
        for($i = 0 ; $i < count($covert_splited); $i++)
        {
            if($j > ($secret_bin_size - 1))
            {
                $final_str .= $covert_splited[$i];
                continue;
            }
            
            
            if(is_keshideh_char($covert_splited[$i]) && !is_ending_char($i, $covert_text))
            {
                if ($secret_bin[$j] == "1" )
                {
                    $final_str .= $covert_splited[$i].$KESHIDEH;
                    $j++;
                }
                else
                {
                    $final_str .= $covert_splited[$i];
                    $j++;
                }
            }
            elseif ($covert_splited[$i] == " ")
            {
                if ($secret_bin[$j] == "1" )
                {
                    $final_str .= $covert_splited[$i]." ";
                    $j++;
                }
                else
                {
                    $final_str .= $covert_splited[$i];
                    $j++;
                }
            }
            else
            {
                $final_str .= $covert_splited[$i];
            }
        }
        
        return $final_str;
    }
    
    function extract_secret($covert_text)
    {
        global $KESHIDEH;
        
        $covert_splited = str_split_unicode($covert_text);
        $secret_bin_str = "";
        
        for($i = 0 ; $i < count($covert_splited); $i++)
        { 
            if(is_keshideh_char($covert_splited[$i]) && !is_ending_char($i, $covert_text))
            {
                if ($covert_splited[$i+1] == $KESHIDEH )
                {
                    $secret_bin_str .= "1";
                    $i++;
                }
                else
                {
                    $secret_bin_str .= "0";
                }
            }
            elseif ($covert_splited[$i] == " ")
            {
                if ($covert_splited[$i+1] == " " )
                {
                    $secret_bin_str .= "1";
                    $i++;
                }
                else
                {
                    $secret_bin_str .= "0";
                }
            }
        }
        
        return translate_bin_to_alefba($secret_bin_str);
    }
    
    function get_covert_text_embedding_capability($covert_text)
    {
        global $KESHIDEH;
        
        $covert_splited = str_split_unicode($covert_text);
        $size = 0;
        
        for($i = 0 ; $i < count($covert_splited); $i++)
        {

            if(is_keshideh_char($covert_splited[$i]) && !is_ending_char($i, $covert_text))
            {
                $size++;
            }
            elseif ($covert_splited[$i] == " ")
            {
                $size++;
            }
        }
        
        return $size;
    }
    
    function normalize_covert_text($covert_text)
    {
        global $KESHIDEH;
        
        $covert_splited = str_split_unicode($covert_text);
        $normalized = "";
        
        for($i = 0 ; $i < count($covert_splited); $i++)
        {

            if($covert_splited[$i] == $KESHIDEH)
            {
                continue;
            }
            elseif ($covert_splited[$i] == " " && $covert_splited[$i+1] == " ")
            {
                $normalized .= $covert_splited[$i];
                $j = 0;
                $k = 0;
                
                while($covert_splited[$i + $j] == " ") $j++;
                $i += ($j - 1);
                continue;
            }
            
            $normalized .= $covert_splited[$i];
        }
        
        return $normalized;
    }
    
    function is_valid_str($text)
    {
        global $FARSI_CHARS, $TERMINATOR;
        
        $text_splited = str_split_unicode($text);
        
        for($i = 0 ; $i < count($text_splited); $i++)
        {
            $pos = get_array_pos($FARSI_CHARS, $text_splited[$i]);
            if ($pos === false || $FARSI_CHARS[$pos] == $TERMINATOR )
                return false;
        }
        
        return true;
    }
    
    if (!empty($_POST['covert_text']) && !empty($_POST['secret_text']) && !empty($_POST['embeded_text']))
    {
        $final_text =  "خطا: شما در هر مرحله تنها میتوانید رمزگشایی و یا رمزنگاری کنید!";
    }
    elseif (!empty($_POST['covert_text']) && !empty($_POST['secret_text']) )
    {
        $covert_text_normal = normalize_covert_text($_POST['covert_text']);
        $secret_text_normal = normalize_covert_text($_POST['secret_text']);
        
        $covert_capability = get_covert_text_embedding_capability($covert_text_normal);
        $secret_text_bit_size = strlen(get_text_bin($secret_text_normal));
        
        if(is_valid_str($secret_text_normal) == false )
        {
            $final_covert_text = "خطا: متن شما کاراکتر هایی داره که غیر قابل مخفی سازی هستند! کاراکتر های قابل مخفی سازی شامل الفبای فارسی، اسپِیس، اعداد، ویرگول، نقطه، علامت تعجب و علامت سوال میشه.";
        }
        else if ($secret_text_bit_size > $covert_capability )
        {
            $final_covert_text =  "خطا: متن شما تنها توانایی مخفی کردن $covert_capability بیت رو داره، در صورتی که متن محرمانه $secret_text_bit_size بیته!";
        }
        else
        {
            $final_covert_text = embed_text($covert_text_normal, $secret_text_normal);
        }
    }
    elseif(!empty($_POST['embeded_text']))
    {
        $final_secret_text = extract_secret($_POST['embeded_text']);
    }
?>
<html>
	<head>
		<title>مخفی کن!(بتا)</title>
		<link href="css/style.css" media="all" rel="stylesheet">
		<script type="text/javascript" src="js/jquery-1.9.1.min.js"></script>
		<script type="text/javascript" src="js/jquery.farsiInput.js"></script>
	</head>
	<body>
		<!--
			There will be no order, only chaos.
			- Snake ;)
		-->
		<div class="wrapper">
			<h3>Lalevoo، مخفی کننده اسرار شما! (بتا)</h3>
			<form action="" method="post">
                <p class="info">توضیحات : از Lalevoo میتوان جهت مخفی سازی یک پیام محرمانه درون یک پیام پوششی دیگر بهره جست. پیام پوششی میتواند یک قطعه شعر، بخشی از یک خبر و یا هر متن دیگری که توجه کسی را جلب نمیکند، باشد. جهت استفاده، متن پوشش را در بخش "متن پوشش" نگاشته و سپس پیام محرمانه را در بخش "پیام محرمانه" بنگارید. سپس خروجی را برای آلیس ارسال کنید. برای دریافت پیام محرمانه درون متن پوشش، آلیس میتواند آن را در قسمت "متن حاوی پیام محرمانه" قرار داده و پیام محرمانه را دریافت کند.<br/><center><b>توجه، Lalevoo در حال حاضر تنها از زبان فارسی پشتیبانی میکند!</center></b></p>
				<br />
                <h4>متن پوشش (ورودی):</h4>
				<textarea id="covert_text" type="text" class="covertText" name="covert_text" ></textarea>
				
				<h4>پیام محرمانه (ورودی):</h4>
				<input id="secret_text" type="text" class="secret" name="secret_text" ><br><br>

                <h4>متن خروجی:</h4>
				<textarea type="text" id="encoded_text" class="covertText" readonly ><?php if(isset($final_covert_text)) echo $final_covert_text; ?></textarea><br><br>
				
				<input class="button" type="submit" alt="Submit" value="رمزنگاری"></input>
                <input class="button" type="button" onclick="randomText()" value="متن تصادفی"></input>
                <br>
				<br /><br />
                <hr>
                <br /><br />
				<h4>متن حاوی پیام محرمانه (ورودی):</h4>
				<textarea id="embeded_text" type="text" class="covertText" name="embeded_text"></textarea><br><br>
				
				<h4>پيام محرمانه (خروجی):</h4>
				<textarea type="text" class="covertText" readonly><?php if(isset($final_secret_text)) echo $final_secret_text; ?></textarea><br><br>
                <input class="button" type="submit" alt="Submit" value="رمزگشایی"></input><br>
			</form>
			<div class="push"></div>
		</div>
		<div id="footer">
			<p>♥ with <a href="https://github.com/shjalayeri" target="_blank">1337ness</a> and </p>
		</div>
    <script type="text/javascript">
        $(function () {
            //$("#secret_text, #covert_text, #embeded_text").farsiInput();
        });

        function randomText() {
            var textArray = [
            'سرماخوردگی یک بیماری واگیردار مربوط به دستگاه تنفسی فوقانی است که عمدتاً بینی را تحت تأثیر قرار می‌دهد. سرماخوردگی معمولاً با خستگی، احساس سرما، عطسه و سردرد آغاز می‌شود و با علائمی چون سرفه، گلودرد، آبریزش بینی و تب ادامه می‌یابد و معمولاً هفت تا ده روز بعد برطرف می‌شود و برخی علائم ممکن است تا سه هفته طول بکشد. بیش از دویست نوع ویروس عامل سرماخوردگی وجود دارد، با این حال راینو ویروس‌ها (که خود بیش از ۹۹ نوع مختلف شناخته‌شده هستند) متدوال‌ترین عامل این بیماری هستند. ویروس‌های عامل بیماری می‌توانند تا مدت زمانی طولانی (برای راینو ویروس تا بیش از ۱۸ ساعت) در محیط زنده بمانند و ممکن است از دستان به چشمان و بینی که محل عفونت هستند، منتقل شوند.',
            'جبر شاخه‌ای از علم ریاضیات است که به مطالعه ساختار و کمیت می‌پردازد. در جبر از نشانه‌ها و معادلات برای نشان دادن ارتباط بین مفاهیم جبری استفاده می‌کنند. متغیرها و ثابت‌های مختلفی در روابط جبری وارد می‌شود و طبق اصول خاصی که برای هر کدام از انواع این معادلات مقرر شده مقادیر متغیرها به دست می‌آید.می‌توان جبر را تعمیم و تجریدی از حساب دانست که در آن بر خلاف حساب عملیاتی مانند جمع و ضرب نه بر اعداد بلکه بر نمادها انجام می‌گیرد. جبر در کنار آنالیز و هندسه یکی از سه شاخه اصلی ریاضیات است. علم جبر نخستین بار از مشرق‌زمین شروع شد و دانشمندانی چون خوارزمی و غیاث‌الدین جمشید کاشانی در این علم تاثیرگذار بودند.',
            'تابع یکی از مفاهیم نظریه مجموعه‌ها و حساب دیفرانسیل و انتگرال است. بطور ساده می‌توان گفت که به قاعده‌های تناظری که به هر ورودی خود یک و فقط یک خروجی نسبت می‌دهند، تابع گفته می‌شود. تابع به عنوان مفهومی در ریاضیات، توسط گوتفرید لایبنیتس در سال ۱۶۹۴، با هدف توصیف یک کمیت در رابطه با یک منحنی مانند شیب یک نمودار در یک نقطه خاص به وجود آمد. امروزه به توابعی که توسط لایبنیز تعریف شدند، توابع مشتق‌پذیر می‌گوییم.',
            'اعتماد در شروع مطلبی با عنوان «آغازي بر پايان مرتضوي» نوشته: حالازماني است براي آغاز كردن پايان مردي به نام «سعيد مرتضوي». انفصال دايم از خدمات قضايي و پنج سال انفصال از خدمات دولتي انتظار مردي را مي كشد كه نيمي از عمرش را در دستگاه قضا بوده است و بعد به دولت كوچ كرده است. روز گذشته خبري منتشر شد كه به نوعي آغازي بود براي پايان دادن به مردي كه اين روزها بيشتر در راه دادسرا است. روز گذشته خبر آمد كه حكم انفصال دايم سعيد مرتضوي از قضاوت، حكم ديوان عالي كشور درباره سعيد مرتضوي يكي از قضات متهم در پرونده حادثه بازداشتگاه كهريزك مبني بر انفصال دايم از خدمات قضايي و پنج سال انفصال از خدمات دولتي، براي اجرا به اجراي احكام ارسال شد.',
            'در همین راستا، در واکنش به خبر تمدید مذاکرات، برخی اعضای کنگره نیز فوراً با صدور بیانیه‌های جداگانه، خواستار وضع تحریم‌های جدید علیه ایران شدند. سناتور مارک کرک، از حزب جمهوریخواه، اعلام کرد که "اکنون بیش از هر وقت، وضع تحریم‌ علیه ایران اقدامی حیاتی است". اد رویس، عضوء کمیته سیاست خارجه کنگره، نیز پس از شنیدن خبر تمدید مذاکرات، طی اطلاعیه‌ای اعلام کرد که "از این هفت ماه تمدید باید برای تشدید فشارهای اقتصادی علیه ایران استفاده کنیم". همچنین، سناتور جان مک‌کین، لیندزی گراهام و کلی آیوت نیز در یک نامه مشترک اعلام کردند که " زمان‌ِ تمدید باید با تحریم‌های قوی‌تر همراه شود". از آن پس نیز، برخی از سیاستمدارن و نمایندگان سنا و مجلس عوام مدام بر ضرورت اعمال تحریم‌های جدید در زمان تمدید مذاکرات پافشاری نموده‌اند.',
            'پس از آنکه مریلا زارعی برای بازی در نقش مادر شهید در فیلم «شیار ۱۴۳» به کارگردانی نرگس آبیار از سوی هیأت داوران جشنواره آسیا ـ پاسیفیک جایزه ویژه دریافت کرد، این اتفاق با موجی از استقبال همراه شد؛ اما این بار در میان استقبال‌کنندگان، چهره‌هایی بودند که مخالف صریح هر نوع جایزه و جشنواره‌ای بودند؛ اتفاقی که نشان می‌دهد زدن همه جشنواره‌ها با یک چوب اشتباهی راهبردی است',
            'روند کاهش قیمت‌های جهانی نفت که در ماه‌های اخیر رخ داده، مورد توجه تحلیلگران و ناظران بین‌المللی قرار گرفته و دلایل مختلفی برای آن ذکر شده که تلاش برخی کشور‌ها برای ضربه زدن به صادرکنندگان فعلی، یکی از آنهاست. در این میان، جدید‌ترین اخبار حاکی از آن است که آمریکا قصد دارد مستقیم‌ وارد «جنگ نفتی» شود که عربستان سعودی مدتی است علیه ایران به راه انداخته است.',
            'نایسر دایسر پلاس اصل وسیله ای چند کاره است که کار در آشپز خانه را آسان می کند. شما می توانیدبا نایسر دایسر پلاس پیاز، گوجه فرنگی و خیار را برای سالاد فصل و سیب زمینی را برای سرخ کردن در زمانی بسیار کوتاه و به راحتی خرد کنید.می توانید با استفاده از تیغه های تیز و با کیفیت نایسر دایسر پلاس در هر ثانیه چندین برش را با هم انجام دهید  . نایسر دایسر پلاس دو مزیت بزرگ دارد یکی ظرف آن است که از پلی کربنیک ساخته شده که ضد خش ، لکه و ضربه است و دیگری آنکه به راحتی تمیز می شود . می توانید آن را آبکشی کنید یا کل آن را در داخل ماشین ظرفشویی بگذارید. به جرات میتوانیم بگوییم نایسر دایسر پلاس واقعا یک خرد کن بی نظیر و جمع و جور است و برای هر اشپزخانه حدالقل یک عدد مورد نیاز است.',
            'دستگاه کوچک کننده بینی آیدان دستگاهی است اختراعی با شماره ثبت اختراع ۳۹۳۷۶ که برای اولین بار در جهان توسط مخترعین ایرانی اختراع و توسط شرکت آیدان طراحی و به تولید انبوه رسیده است.در ابتدا ممکن است قابل باور نباشد که بتوان بینی را بدون عمل جراحی کوچک ساخت اما باید گفت در طول تاریخ اختراعات ، همیشه هر اختراعی در ابتدا قابل باور نبوده است.دستگاه کوچک کننده بینی نیز از این قاعده مستثنی نیست در سال اول تولید شاید هیچ کس نمی دانست که این دستگاه با چنین استقبالی از سوی مصرف کنندگان روبرو خواهد شد.',
            'سلام، حالت چطوره؟ مامان اینا خوب هستن؟ امروز بعداظهر میخایم بیایم خونتون. به همسرت بگو که توی زحمت نیفته. راستی جواد هم هست؟ خیلی وقته که نددیمش، اگر دیدیش حتما بهش سلام برسون و بگو نامرد کجا غیبت زد، به ما هم سر بزن. امروز عصر ساعت چهار میبینمت. مواظبت کن.',
            'می خور که به زیر گل بسی خواهی خفت\
            بی مونس  و بی رفیق  و بی همدم و جفت\
            زنهار  به  کس  مگو  تو   این  راز  نهفت\
            هر  لاله   که   پژمرد    نخواهد    بشکفت'
            ];
            var randomNumber = Math.floor(Math.random() * textArray.length);
            var covert_text = document.getElementById("covert_text")
            covert_text.innerHTML = textArray[randomNumber];
        }
    </script>
	</body>
</html>

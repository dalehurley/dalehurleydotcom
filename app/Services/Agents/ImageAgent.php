<?php

namespace App\Services\Agents;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use OpenAI\Laravel\Facades\OpenAI;
use App\Services\ImageProcessor;
use App\Services\ImageCompressionService;
use App\Services\ThumbnailService;

class ImageAgent
{
    /**
     * Generate an image for a blog post and save it.
     *
     * @param array $blogPost
     * @return array ['image_path' => string, 'thumbnail_path' => string]
     */
    public static function generateImage(array $blogPost): array
    {
        // Validate required fields
        if (!isset($blogPost['title']) || !isset($blogPost['slug'])) {
            throw new \InvalidArgumentException('Blog post must have title and slug fields');
        }

        $imagePrompt = self::generateImagePrompt($blogPost);
        Log::info("Generated Image Prompt for '{$blogPost['title']}':\n{$imagePrompt}");

        $response = OpenAI::images()->create([
            'model' => 'gpt-image-1',
            'prompt' => $imagePrompt,
            'size' => '1536x1024',
            'output_format' => 'webp'
        ]);

        $imageBase64 = $response->data[0]->b64_json;
        $contents = base64_decode($imageBase64);

        // Save to public/images directory
        $imagePath = 'images/' . $blogPost['slug'] . '.webp';
        $thumbnailPath = 'images/' . $blogPost['slug'] . '-thumbnail.webp';

        $publicImagePath = public_path($imagePath);
        $publicThumbnailPath = public_path($thumbnailPath);

        // Ensure directory exists
        File::ensureDirectoryExists(dirname($publicImagePath));

        // Save original image
        file_put_contents($publicImagePath, $contents);

        // Compress the main image using CLI tools
        try {
            $compressor = app(ImageCompressionService::class);
            $optimized = $compressor->compress($publicImagePath, 'webp');
            file_put_contents($publicImagePath, $optimized['binary']);

            Log::info("Main image compressed", [
                'slug' => $blogPost['slug'],
                'original_kb' => $optimized['original_kb'],
                'compressed_kb' => $optimized['compressed_kb'],
                'savings_pct' => $optimized['savings_pct']
            ]);
        } catch (\Exception $e) {
            Log::warning("Failed to compress main image for {$blogPost['slug']}, falling back to ImageProcessor: " . $e->getMessage());
            // Fallback to ImageProcessor optimization
            try {
                ImageProcessor::optimizeImage($publicImagePath, $publicImagePath, 90, 1920, 1080);
            } catch (\Exception $fallbackError) {
                Log::warning("ImageProcessor fallback also failed for {$blogPost['slug']}: " . $fallbackError->getMessage());
            }
        }

        // Create thumbnail using ThumbnailService
        $thumbnailService = app(ThumbnailService::class);
        $thumbnailResult = $thumbnailService->createThumbnail(
            $publicImagePath,
            $publicThumbnailPath,
            384,
            256,
            85,
            $blogPost['slug']
        );

        if (!$thumbnailResult['success']) {
            Log::warning("Failed to create thumbnail for {$blogPost['slug']}: " . $thumbnailResult['error']);
            // Continue without thumbnail - we still have the main image
        }

        return [
            'image_path' => $imagePath,
            'thumbnail_path' => $thumbnailPath
        ];
    }

    /**
     * Generate a prompt for the image generation AI.
     *
     * @param array $blogPost
     * @return string
     */
    private static function generateImagePrompt(array $blogPost): string
    {
        $metaPrompt = <<<PROMPT
You are an elite visual prompt engineer with a flair for mid-century advertising art. Create a SINGLE, production-ready prompt that an AI image generator (e.g. Midjourney, DALL·E) can use to produce a striking hero/featured image for the following blog post title: "{$blogPost['title']}".

Use the following blog post content for inspiration:
<blog-post-content>
{$blogPost['content']}
</blog-post-content>

The finished artwork must feel like a 1950s comic-style propaganda/pin-up poster while representing Dale Hurley's brand promise—delivering innovative AI solutions and empowering businesses through cutting-edge technology.

### Creative Vision Framework
PROMPT;

        // Generate random variations for maximum diversity
        $characters = [
            'Confident brunette with vintage glasses',
            'Athletic blonde with determined expression',
            'Elegant redhead with mysterious smile',
            'Distinguished silver-haired professional',
            'Charismatic dark-haired innovator',
            'Dynamic duo of tech partners',
            'Diverse team of three AI pioneers',
            'Rugged masculine inventor',
            'Graceful feminine scientist',
            'Non-binary tech visionary',
            'Scholarly professor with bow tie',
            'Adventurous explorer with hiking gear',
            'Sophisticated businesswoman with briefcase',
            'Young prodigy with creative energy',
            'Seasoned veteran with wise eyes',
            'Energetic entrepreneur with bright smile',
            'Mysterious figure in lab coat',
            'Charismatic leader addressing crowd',
            'Focused researcher examining data',
            'Bold pioneer breaking barriers',
            'Innovative designer sketching concepts',
            'Determined engineer solving problems',
            'Inspiring mentor guiding others',
            'Creative artist blending mediums',
            'Strategic planner with vision board',
            'Tech-savvy teenager with laptop',
            'Experienced craftsperson with tools',
            'Passionate advocate speaking truth',
            'Calm meditation teacher with wisdom',
            'Dynamic athlete in training mode',
            'Stylish fashionista setting trends',
            'Intellectual philosopher pondering deeply',
            'Practical farmer with innovative methods',
            'Musical composer creating symphonies',
            'Medical professional saving lives',
            'Environmental scientist protecting nature',
            'Space explorer reaching for stars',
            'Cultural anthropologist studying societies',
            'Brilliant mathematician solving equations',
            'Skilled chef creating culinary art',
            'Master architect designing futures',
            'Talented photographer capturing moments',
            'Dedicated teacher inspiring students',
            'Compassionate social worker helping others',
            'Fearless journalist uncovering truth',
            'Innovative startup founder disrupting markets',
            'Experienced pilot navigating challenges',
            'Creative writer crafting stories',
            'Determined athlete breaking records',
            'Wise elder sharing knowledge'
        ];

        $outfits = [
            'lab coat and safety goggles',
            'mechanic coveralls and tool belt',
            'pilot jacket and aviator glasses',
            'chef apron and tall hat',
            'military uniform with medals',
            'sharp business suit and briefcase',
            'nurse uniform and stethoscope',
            'cowboy boots and ranch wear',
            'sailor outfit and captain hat',
            'artist smock and beret',
            'detective coat and magnifying glass',
            'astronaut suit and helmet',
            'teacher cardigan and glasses',
            'construction hard hat and vest',
            'librarian sweater and cat-eye glasses',
            'racing jumpsuit and helmet',
            'garden overalls and sun hat',
            'vintage swimwear and sunglasses',
            'firefighter gear and helmet',
            'police uniform and badge',
            'doctor white coat and stethoscope',
            'scientist lab attire and clipboard',
            'engineer hard hat and blueprints',
            'farmer denim overalls and straw hat',
            'chef whites and neckerchief',
            'waitress uniform and apron',
            'bartender vest and bow tie',
            'barber striped shirt and suspenders',
            'postman uniform and mail bag',
            'taxi driver cap and jacket',
            'train conductor uniform and whistle',
            'park ranger outfit and badge',
            'lifeguard red swimsuit and whistle',
            'circus performer sequined costume',
            'magician tuxedo and top hat',
            'dancer leotard and tutu',
            'musician formal attire and bow tie',
            'photographer vest with camera straps',
            'journalist trench coat and notebook',
            'radio DJ headphones and casual shirt',
            'TV anchor suit and microphone',
            'fashion designer measuring tape and pins',
            'hairdresser apron and styling tools',
            'makeup artist utility belt and brushes',
            'florist apron and garden gloves',
            'baker white uniform and flour dusting',
            'butcher apron and cleaver',
            'fisherman waders and tackle box',
            'hunter camouflage and rifle',
            'mountaineer climbing gear and ropes',
            'diver wetsuit and oxygen tank',
            'skier winter jacket and goggles',
            'surfer board shorts and rash guard',
            'cyclist spandex and aerodynamic helmet',
            'boxer shorts and gloves',
            'tennis player whites and racket',
            'golfer polo and visor',
            'baseball player uniform and cap',
            'football player jersey and helmet',
            'basketball player shorts and sneakers',
            'soccer player cleats and shin guards',
            'hockey player gear and stick',
            'wrestler singlet and headgear',
            'gymnast leotard and chalk',
            'swimmer goggles and cap',
            'track runner spikes and shorts',
            'weightlifter tank top and belt',
            'martial arts gi and black belt',
            'yoga instructor comfortable attire',
            'personal trainer workout gear',
            'massage therapist scrubs and oils',
            'veterinarian coat and animal treats',
            'dentist scrubs and tools',
            'optometrist white coat and eye chart',
            'pharmacist lab coat and pill bottles',
            'therapist professional attire and notepad',
            'social worker casual blazer and briefcase',
            'lawyer pin-stripe suit and briefcase',
            'judge robes and gavel',
            'accountant conservative suit and calculator',
            'banker formal attire and gold watch',
            'real estate agent professional dress and keys',
            'insurance agent suit and clipboard',
            'salesperson polished appearance and samples',
            'secretary blouse and pearls',
            'receptionist phone headset and smile',
            'security guard uniform and flashlight',
            'janitor coveralls and cleaning supplies',
            'delivery driver uniform and packages',
            'mechanic grease-stained overalls',
            'electrician tool belt and hard hat',
            'plumber coveralls and pipe wrench',
            'carpenter tool belt and measuring tape',
            'painter overalls and brush',
            'roofer safety gear and shingles',
            'landscaper work clothes and pruning shears',
            'welder protective mask and torch',
            'blacksmith leather apron and hammer',
            'jeweler magnifying glass and tools',
            'watchmaker precision instruments',
            'tailor measuring tape and pins',
            'cobbler leather apron and tools',
            'potter clay-stained apron',
            'glassblower heat-resistant gear',
            'woodworker safety glasses and chisel',
            'metalworker protective equipment',
            'textile worker industrial uniform',
            'factory worker safety gear and badge'
        ];

        $settings = [
            'retro laboratory with bubbling beakers',
            'vintage garage with classic cars',
            '1950s diner with neon signs',
            'old-school barbershop with spinning pole',
            'classic library with towering shelves',
            'antique observatory with telescope',
            'retro train station with steam engine',
            'vintage market with fresh produce',
            'classic theater with spotlights',
            'old factory with massive machinery',
            'retro submarine with periscopes',
            'vintage airfield with propeller planes',
            'classic workshop with hand tools',
            'old-timey circus with big top',
            'retro space station with control panels',
            'art deco skyscraper with geometric patterns',
            'vintage record studio with analog equipment',
            'classic soda fountain with chrome stools',
            'retro bowling alley with pin setters',
            'old-fashioned pharmacy with medicine bottles',
            'vintage radio station with broadcasting booth',
            'classic dance hall with mirror ball',
            'retro drive-in movie theater',
            'old-timey newspaper office with printing press',
            'vintage television studio with cameras',
            'classic ice cream parlor with checkered floor',
            'retro gas station with full service',
            'old-school gymnasium with rope climbing',
            'vintage beauty salon with hair dryers',
            'classic photography darkroom',
            'retro arcade with pinball machines',
            'old-fashioned clockmaker shop',
            'vintage hat shop with display cases',
            'classic tailor shop with mannequins',
            'retro milk bar with delivery trucks',
            'old-timey general store with wooden shelves',
            'vintage jewelry shop with display cases',
            'classic shoe repair shop with tools',
            'retro candy store with glass jars',
            'old-fashioned bakery with brick ovens',
            'vintage flower shop with arrangements',
            'classic typewriter repair shop',
            'retro toy store with wooden toys',
            'old-timey post office with mail slots',
            'vintage bank with teller windows',
            'classic fire station with red truck',
            'retro police station with holding cells',
            'old-school hospital with white uniforms',
            'vintage courthouse with marble columns',
            'classic city hall with flag pole',
            'retro hotel lobby with bell hops',
            'old-fashioned restaurant kitchen',
            'vintage speakeasy with hidden entrance',
            'classic jazz club with stage lights',
            'retro nightclub with dance floor',
            'old-timey saloon with swinging doors',
            'vintage coffee house with bean roaster',
            'classic tea room with fine china',
            'retro malt shop with soda jerks',
            'old-fashioned delicatessen with hanging meats',
            'vintage fish market with ice displays',
            'classic butcher shop with meat hooks',
            'retro grocery store with checkout lanes',
            'old-timey hardware store with bins',
            'vintage furniture store with showroom',
            'classic appliance store with demonstrations',
            'retro electronics shop with vacuum tubes',
            'old-fashioned music store with instruments',
            'vintage bookstore with reading nooks',
            'classic art supply store with easels',
            'retro sporting goods store with equipment',
            'old-timey pet store with cages',
            'vintage garden center with greenhouses',
            'classic auto parts store with shelves',
            'retro motorcycle shop with repair bay',
            'old-fashioned bicycle shop with tools',
            'vintage boat dock with sailboats',
            'classic airport terminal with propellers',
            'retro bus depot with schedule boards',
            'old-timey taxi stand with checkers',
            'vintage subway platform with tiles',
            'classic trolley stop with overhead wires',
            'retro construction site with cranes',
            'old-fashioned farm with red barn',
            'vintage ranch with wooden fences',
            'classic lighthouse with rotating beacon',
            'retro beach boardwalk with carnival rides',
            'old-timey mountain cabin with stone chimney',
            'vintage desert outpost with water tower',
            'classic forest ranger station with lookout',
            'retro mining camp with equipment',
            'old-fashioned oil derrick with pumps',
            'vintage steel mill with furnaces',
            'classic shipyard with massive cranes',
            'retro airplane hangar with mechanics',
            'old-timey blacksmith forge with anvil',
            'vintage pottery studio with kilns',
            'classic glass blowing workshop',
            'retro textile mill with looms',
            'old-fashioned printing house with presses',
            'vintage photography studio with lights',
            'classic art studio with canvases',
            'retro music recording booth',
            'old-timey radio repair shop',
            'vintage computer room with mainframes',
            'classic science laboratory with equipment',
            'retro weather station with instruments',
            'old-fashioned telegraph office',
            'vintage switchboard room with operators',
            'classic control room with monitors',
            'retro command center with radar screens',
            'old-timey war room with maps',
            'vintage bunker with steel doors',
            'classic fallout shelter with supplies',
            'retro rocket launch pad with towers',
            'old-fashioned mission control',
            'vintage space capsule interior',
            'classic alien laboratory with specimens',
            'retro time machine chamber',
            'old-timey mad scientist lair',
            'vintage robot factory with assembly lines',
            'classic cyborg repair facility',
            'retro android testing laboratory',
            'old-fashioned steampunk workshop',
            'vintage clockwork mechanism room',
            'classic gear and cog factory',
            'retro steam engine room',
            'old-timey power plant with turbines',
            'vintage electrical grid control room',
            'classic nuclear reactor facility',
            'retro solar panel installation',
            'old-fashioned windmill farm',
            'vintage hydroelectric dam',
            'classic geothermal plant',
            'retro coal mining operation',
            'old-timey oil refinery with towers',
            'vintage chemical plant with pipes',
            'classic pharmaceutical laboratory',
            'retro food processing facility',
            'old-fashioned brewery with vats',
            'vintage distillery with copper stills',
            'classic vineyard with grape vines',
            'retro greenhouse with tropical plants',
            'old-timey aquarium with fish tanks',
            'vintage zoo with animal enclosures',
            'classic circus tent with rings',
            'retro carnival midway with games',
            'old-fashioned amusement park with rides',
            'vintage roller rink with disco ball',
            'classic swimming pool with diving board',
            'retro tennis court with net',
            'old-timey golf course with flags',
            'vintage baseball diamond with stands',
            'classic football stadium with lights',
            'retro basketball court with hoops',
            'old-fashioned boxing ring with ropes',
            'vintage wrestling arena with mats',
            'classic track and field stadium',
            'retro ice skating rink with zamboni',
            'old-timey ski lodge with fireplace',
            'vintage beach resort with umbrellas',
            'classic mountain retreat with views',
            'retro desert spa with cacti',
            'old-fashioned country club with golf carts',
            'vintage yacht club with sailboats',
            'classic fishing lodge with tackle',
            'retro hunting cabin with trophies',
            'old-timey camping ground with tents',
            'vintage RV park with hookups',
            'classic motel with neon vacancy sign',
            'retro roadside diner with chrome',
            'old-fashioned truck stop with fuel pumps',
            'vintage rest area with picnic tables',
            'classic scenic overlook with telescope',
            'retro visitor center with maps',
            'old-timey museum with exhibits',
            'vintage planetarium with dome',
            'classic aquarium with whale tanks',
            'retro science center with demonstrations',
            'old-fashioned cultural center with stages',
            'vintage community center with activities',
            'classic convention center with booths',
            'retro exhibition hall with displays',
            'old-timey fairground with Ferris wheel',
            'vintage flea market with antiques',
            'classic farmers market with produce',
            'retro craft fair with artisans',
            'old-fashioned county fair with livestock',
            'vintage street festival with vendors',
            'classic parade route with floats',
            'retro block party with neighbors',
            'old-timey town square with gazebo',
            'vintage main street with storefronts',
            'classic downtown district with skyscrapers',
            'retro suburban neighborhood with lawns',
            'old-fashioned rural crossroads with signs',
            'vintage ghost town with tumbleweeds',
            'classic frontier settlement with saloons',
            'retro space colony with domes',
            'old-timey underwater city with bubbles',
            'vintage floating platform with anchors',
            'classic tree house village with bridges',
            'retro underground bunker with tunnels',
            'old-fashioned cave dwelling with torches',
            'vintage ice castle with sculptures',
            'classic sand castle with moats',
            'retro cloud city with airships',
            'old-timey dimension portal with energy',
            'vintage parallel universe with mirrors',
            'classic alternate timeline with differences',
            'retro dream sequence with surreal elements',
            'old-fashioned memory palace with corridors',
            'vintage imagination land with creatures',
            'classic wonderland with impossible geometry'
        ];

        $actions = [
            'operating complex machinery with precision',
            'conducting scientific experiments with curiosity',
            'building architectural marvels with determination',
            'performing on stage with passion',
            'racing vintage vehicles at breakneck speed',
            'solving intricate puzzles with focused intensity',
            'teaching revolutionary concepts to eager students',
            'exploring uncharted territories with courage',
            'creating artistic masterpieces with flowing creativity',
            'leading innovative teams toward breakthrough solutions',
            'discovering hidden treasures in ancient ruins',
            'inventing revolutionary devices in cluttered workshops',
            'debugging complex code with laser focus',
            'analyzing data patterns on multiple screens',
            'assembling robotic components with steady hands',
            'calibrating sensitive instruments with care',
            'welding metal structures with sparks flying',
            'mixing chemical compounds in beakers',
            'sketching blueprints on large drafting tables',
            'operating printing presses with rhythmic motion',
            'navigating spacecraft through asteroid fields',
            'climbing radio towers to adjust antennas',
            'repairing vintage automobiles under the hood',
            'photographing specimens through microscopes',
            'broadcasting live from radio stations',
            'performing surgery with surgical precision',
            'cooking gourmet meals in bustling kitchens',
            'delivering passionate speeches to crowds',
            'dancing energetically on polished floors',
            'playing musical instruments with soul',
            'painting murals on expansive walls',
            'sculpting marble with artistic vision',
            'writing stories on vintage typewriters',
            'reading from leather-bound volumes',
            'teaching children in bright classrooms',
            'training athletes on practice fields',
            'caring for patients in hospital wards',
            'fighting fires with hoses and ladders',
            'arresting criminals with authority',
            'directing traffic at busy intersections',
            'delivering mail door to door',
            'farming crops in fertile fields',
            'fishing from weathered docks',
            'hunting in dense forest wilderness',
            'mining precious metals underground',
            'logging trees with powerful saws',
            'constructing buildings with cranes',
            'inspecting quality on assembly lines',
            'loading cargo onto ships',
            'driving trucks across country highways',
            'piloting airplanes through cloudy skies',
            'sailing boats across choppy waters',
            'riding motorcycles on winding roads',
            'cycling through scenic mountain paths',
            'hiking up steep rocky trails',
            'skiing down powdery white slopes',
            'surfing massive ocean waves',
            'swimming through crystal clear pools',
            'diving into mysterious underwater caves',
            'rock climbing vertical cliff faces',
            'parachuting from high altitudes',
            'bungee jumping from tall bridges',
            'competing in boxing rings',
            'wrestling on gymnasium mats',
            'running track events at stadiums',
            'playing tennis on manicured courts',
            'golfing on emerald green fairways',
            'bowling strikes at vintage alleys',
            'shooting arrows at precise targets',
            'fencing with elegant swordplay',
            'practicing martial arts with discipline',
            'lifting weights in mirrored gyms',
            'stretching in peaceful yoga studios',
            'meditating in serene garden settings',
            'massaging clients in spa environments',
            'cutting hair in bustling salons',
            'applying makeup with artistic flair',
            'designing clothes on dress forms',
            'tailoring suits with measuring tapes',
            'cobbling shoes with traditional tools',
            'crafting jewelry with precision instruments',
            'repairing watches with magnifying glasses',
            'binding books in paper-filled workshops',
            'printing newspapers on massive presses',
            'developing photographs in darkrooms',
            'editing films on vintage equipment',
            'recording music in soundproof studios',
            'broadcasting television from control rooms',
            'operating radio transmitters',
            'programming computers with punch cards',
            'calculating equations on blackboards',
            'surveying land with transit instruments',
            'mapping territories with compass and ruler',
            'forecasting weather with barometers',
            'studying stars through telescopes',
            'examining fossils with brushes',
            'cataloging specimens in museums',
            'excavating archaeological sites carefully',
            'translating ancient texts',
            'deciphering mysterious codes',
            'investigating crime scenes methodically',
            'interrogating suspects in stark rooms',
            'testifying in courtroom proceedings',
            'negotiating business deals confidently',
            'selling products with enthusiastic presentations',
            'managing offices with organizational skill',
            'accounting finances with ledgers',
            'banking transactions at teller windows',
            'insuring properties against disasters',
            'real estate touring with prospective buyers',
            'auctioning valuable items energetically',
            'bartending cocktails with flair',
            'waiting tables in busy restaurants',
            'hosting guests at elegant receptions',
            'cleaning buildings with industrial equipment',
            'maintaining gardens with horticultural expertise',
            'landscaping yards with creative vision',
            'exterminating pests with professional tools',
            'securing buildings with vigilant patrol',
            'guarding valuables in armored vehicles',
            'rescuing victims from dangerous situations',
            'providing first aid to injured persons',
            'counseling troubled individuals compassionately',
            'social working with disadvantaged communities',
            'volunteering at charitable organizations',
            'protesting injustices with raised signs',
            'campaigning for political candidates',
            'voting in democratic elections',
            'governing communities with wisdom',
            'legislating laws in marble halls',
            'judging cases with balanced fairness',
            'enforcing regulations with authority',
            'inspecting facilities for compliance',
            'auditing books for accuracy',
            'consulting businesses for improvements',
            'training employees in conference rooms',
            'recruiting talent for growing companies',
            'interviewing candidates professionally',
            'promoting products through advertising campaigns',
            'marketing services to target audiences',
            'public relations managing with media',
            'event planning elaborate celebrations',
            'catering delicious food for gatherings',
            'decorating venues with artistic touches',
            'entertaining crowds with comedy',
            'acting in theatrical productions',
            'directing films with creative vision',
            'producing shows behind the scenes',
            'writing scripts for television',
            'editing manuscripts for publication',
            'publishing books for eager readers',
            'reviewing literature critically',
            'criticizing art with educated perspective',
            'curating exhibitions in galleries',
            'touring groups through historical sites',
            'guiding expeditions through wilderness',
            'translating languages for international communication',
            'interpreting conversations in real time',
            'teaching foreign languages enthusiastically',
            'studying cultures anthropologically',
            'researching history in archives',
            'preserving artifacts for future generations',
            'restoring paintings to original beauty',
            'conserving wildlife in natural habitats',
            'protecting environments from destruction',
            'campaigning for ecological awareness',
            'recycling materials responsibly',
            'composting organic waste naturally',
            'generating renewable energy sustainably',
            'innovating green technologies',
            'developing sustainable solutions',
            'implementing efficiency improvements',
            'optimizing processes systematically',
            'automating repetitive tasks intelligently',
            'integrating systems seamlessly',
            'troubleshooting technical problems',
            'upgrading legacy infrastructure',
            'migrating data securely',
            'backing up critical information',
            'monitoring system performance continuously',
            'analyzing metrics for insights',
            'reporting findings comprehensively',
            'presenting results professionally',
            'demonstrating products effectively',
            'training users on new systems',
            'supporting customers with technical issues',
            'maintaining equipment preventively',
            'repairing broken machinery expertly',
            'replacing worn components efficiently',
            'installing new technology carefully',
            'configuring settings optimally',
            'testing functionality thoroughly',
            'validating results scientifically',
            'documenting procedures meticulously',
            'archiving records systematically',
            'organizing files logically',
            'scheduling appointments efficiently',
            'coordinating events seamlessly',
            'communicating updates clearly',
            'collaborating across teams effectively',
            'leading meetings productively',
            'facilitating discussions constructively',
            'mediating conflicts diplomatically',
            'negotiating agreements fairly',
            'building relationships authentically',
            'networking professionally',
            'mentoring newcomers generously',
            'coaching performance improvements',
            'developing talent strategically',
            'succession planning thoughtfully',
            'change management skillfully',
            'crisis management calmly',
            'risk assessment thoroughly',
            'quality assurance rigorously',
            'compliance monitoring strictly',
            'safety training comprehensively',
            'emergency response quickly',
            'disaster recovery systematically',
            'business continuity planning',
            'strategic planning visionary',
            'tactical execution precisely',
            'operational excellence consistently',
            'performance optimization continuously',
            'innovation fostering creatively',
            'transformation leading boldly',
            'disruption embracing adaptively',
            'evolution guiding wisely',
            'revolution starting courageously',
            'breakthrough achieving triumphantly'
        ];

        $colorPalettes = [
            'sunset orange, deep purple, and gold',
            'electric blue, hot pink, and silver',
            'forest green, burgundy, and copper',
            'coral, navy, and cream',
            'turquoise, rust, and ivory',
            'violet, amber, and charcoal',
            'crimson, sage, and bronze',
            'magenta, teal, and pearl',
            'ruby, mint, and graphite'
        ];

        $props = [
            'vintage computers and calculators',
            'classic tools and instruments',
            'retro vehicles and transportation',
            'musical instruments and equipment',
            'scientific apparatus and gadgets',
            'art supplies and creative tools',
            'sports equipment and gear',
            'kitchen appliances and utensils',
            'communication devices and radios',
            'vintage cameras and photography equipment',
            'antique typewriters and printing machines',
            'classic laboratory beakers and test tubes',
            'retro robots and mechanical automatons',
            'vintage medical equipment and stethoscopes',
            'classic aviation instruments and propellers',
            'antique telescopes and navigational tools',
            'retro gaming machines and pinball devices',
            'vintage industrial machinery and gears',
            'classic electrical components and circuits',
            'antique clockwork mechanisms and springs',
            'retro space age technology and satellites',
            'vintage farming tools and agricultural equipment',
            'classic construction machinery and blueprints',
            'antique weapons and military equipment',
            'retro beauty salon equipment and hair dryers',
            'vintage dental tools and medical charts',
            'classic fire fighting equipment and helmets',
            'antique surveying instruments and maps',
            'retro chemistry sets and periodic tables',
            'vintage sewing machines and fabric tools',
            'classic woodworking tools and workbenches',
            'antique metalworking equipment and anvils',
            'retro automotive parts and engine components',
            'vintage kitchen scales and measuring devices',
            'classic optical equipment and magnifying glasses',
            'antique weather instruments and barometers',
            'retro electronic tubes and vacuum components',
            'vintage printing blocks and typography tools',
            'classic diving equipment and oxygen tanks',
            'antique mining equipment and pickaxes',
            'retro broadcasting equipment and microphones',
            'vintage laboratory microscopes and slides',
            'classic fishing gear and tackle boxes',
            'antique hunting equipment and compasses',
            'retro textile machinery and looms',
            'vintage jewelry making tools and gems',
            'classic watch repair instruments and gears',
            'antique shoe cobbling tools and leather',
            'retro barbershop equipment and razors',
            'vintage tailoring tools and mannequins',
            'classic pottery wheels and ceramic tools',
            'antique glassblowing equipment and furnaces',
            'retro carpentry planes and chisels',
            'vintage blacksmith hammers and forges',
            'classic painting easels and palettes',
            'antique musical notation and sheet music',
            'retro stage lighting and theater equipment',
            'vintage dance props and costume accessories',
            'classic circus equipment and performance tools',
            'antique magic tricks and illusion devices',
            'retro carnival games and prize wheels',
            'vintage amusement park ride mechanisms',
            'classic playground equipment and swings',
            'antique toy trains and model railways',
            'retro dollhouses and miniature furniture',
            'vintage board games and playing cards',
            'classic puzzle pieces and brain teasers',
            'antique books and leather-bound volumes',
            'retro magazines and newspaper headlines',
            'vintage postcards and travel memorabilia',
            'classic maps and geographical charts',
            'antique coins and currency collections',
            'retro stamps and postal equipment',
            'vintage bottles and glass containers',
            'classic teapots and fine china',
            'antique silverware and serving platters',
            'retro lunch boxes and thermoses',
            'vintage picnic baskets and outdoor gear',
            'classic camping equipment and lanterns',
            'antique luggage and travel trunks',
            'retro fashion accessories and jewelry',
            'vintage handbags and leather goods',
            'classic umbrellas and walking canes',
            'antique pocket watches and timepieces',
            'retro sunglasses and eyewear',
            'vintage hats and millinery accessories',
            'classic shoes and footwear displays',
            'antique perfume bottles and cosmetics',
            'retro hair styling tools and accessories',
            'vintage cleaning supplies and household items',
            'classic gardening tools and flower pots',
            'antique watering cans and plant stands',
            'retro lawn furniture and outdoor decor',
            'vintage birdbaths and garden ornaments',
            'classic wind chimes and decorative elements',
            'antique weather vanes and roof decorations',
            'retro mailboxes and house numbers',
            'vintage door knockers and hardware',
            'classic window shutters and awnings',
            'antique lamp posts and street lighting',
            'retro neon signs and advertising displays',
            'vintage store fixtures and cash registers',
            'classic restaurant equipment and coffee makers',
            'antique vending machines and coin operators',
            'retro jukebox machines and record players',
            'vintage television sets and radio consoles',
            'classic telephone booths and communication devices',
            'antique clocks and timing mechanisms',
            'retro alarm systems and security devices',
            'vintage locks and key collections',
            'classic safes and storage containers',
            'antique filing cabinets and office furniture',
            'retro desk accessories and paperweights',
            'vintage fountain pens and writing instruments',
            'classic ink wells and blotting paper',
            'antique rubber stamps and seal makers',
            'retro calculators and adding machines',
            'vintage slide rules and mathematical instruments',
            'classic drafting tools and architectural supplies',
            'antique measuring devices and rulers',
            'retro weight scales and balance mechanisms',
            'vintage thermometers and temperature gauges',
            'classic pressure gauges and monitoring devices',
            'antique electrical meters and testing equipment',
            'retro power tools and workshop machinery',
            'vintage hand tools and hardware collections',
            'classic nuts and bolts assortments',
            'antique rope and chain displays',
            'retro pulley systems and mechanical devices',
            'vintage motors and engine parts',
            'classic gears and transmission components',
            'antique wheels and axle assemblies',
            'retro brake systems and automotive parts',
            'vintage fuel pumps and service station equipment',
            'classic traffic signals and road signs',
            'antique railroad equipment and train parts',
            'retro ship components and maritime tools',
            'vintage airplane parts and aviation instruments',
            'classic space equipment and rocket components',
            'antique submarine periscopes and naval devices',
            'retro tank treads and military hardware',
            'vintage artillery shells and ammunition displays',
            'classic armor pieces and protective equipment',
            'antique swords and medieval weapons',
            'retro shields and defensive gear',
            'vintage flags and patriotic symbols',
            'classic medals and military decorations',
            'antique uniforms and ceremonial attire',
            'retro badges and insignia collections'
        ];

        $compositions = [
            'dynamic action shot with motion blur',
            'intimate close-up with intense focus',
            'wide establishing shot with epic scope',
            'symmetrical balanced composition',
            'asymmetrical dynamic arrangement',
            'layered depth with foreground/background',
            'circular vortex composition',
            'diagonal leading lines',
            'triangular power pose'
        ];

        // Randomly select elements
        $selectedCharacter = $characters[array_rand($characters)];
        $selectedOutfit = $outfits[array_rand($outfits)];
        $selectedSetting = $settings[array_rand($settings)];
        $selectedAction = $actions[array_rand($actions)];
        $selectedColors = $colorPalettes[array_rand($colorPalettes)];
        $selectedProps = $props[array_rand($props)];
        $selectedComposition = $compositions[array_rand($compositions)];

        // Optional banner (50% chance)
        $includeBanner = rand(0, 1) === 1;
        $bannerText = $includeBanner ? "Include an attention-grabbing banner with ≤ 8 words that captures the blog's essence. " : "";

        // Optional creative wildcards (30% chance each)
        $wildcards = [];
        if (rand(0, 9) < 3) $wildcards[] = "AI-powered robotic companion";
        if (rand(0, 9) < 3) $wildcards[] = "dramatic weather effects";
        if (rand(0, 9) < 3) $wildcards[] = "retro-futuristic technology blend";
        if (rand(0, 9) < 3) $wildcards[] = "environmental storytelling details";

        $wildcardText = !empty($wildcards) ? "Include: " . implode(", ", $wildcards) . ". " : "";

        $metaPrompt .= <<<PROMPT

**Creative Framework for Maximum Variation:**

{$bannerText}Create a {$selectedComposition} featuring a {$selectedCharacter} wearing {$selectedOutfit} in a {$selectedSetting}, {$selectedAction}. Use color palette of {$selectedColors}. Include {$selectedProps} as key visual elements. {$wildcardText}

**Art Direction:**
• Style: 1950s propaganda/pin-up poster/comic book advertisement with thick black outlines, vintage advertising/poster aesthetic, halftone shading, slight paper texture overlay.
• Mood: Confident empowerment, playful innovation, retro glamour meets high-tech revolution, explosive energy, empowering transformation, playful innovation, confident optimism.
• Aspect Ratio: Landscape (3:2) optimized for hero images
• Avoid: Generic corporate aesthetics, predictable compositions

**Aspect Ratio**: Landscape (3:2) optimized for hero images with dynamic composition.

**Creative Mandate:** This must feel completely unique.

Be creative and have fun with the concept and make sure in some way it reflects the blog post's content and Dale Hurley's brand identity as a leader in AI innovation. The image should be visually striking, memorable, and perfectly suited for a blog post header.

### Prompt Output Rules
• Start with a direct instruction to the generator (e.g. "Create a vibrant 1950s propaganda pin-up poster…")
• Include all stylistic directives above in concise, generator-friendly syntax
• **Do NOT wrap your prompt in back-ticks or markdown**
• Return ONLY the prompt—no commentary, pre-amble, or closing remarks
• Ensure composition works well in 3:2 aspect ratio with room for headline and tagline placement
• Make sure the image captures the blog post essence with maximum visual impact and fun factor

Generate the image prompt now.
PROMPT;

        $response = OpenAI::chat()->create([
            'model' => 'o3',
            'messages' => [
                ['role' => 'user', 'content' => $metaPrompt],
            ],
        ]);

        Log::info('Response: ' . json_encode($response->choices[0]->message->content));

        return $response->choices[0]->message->content;
    }
}

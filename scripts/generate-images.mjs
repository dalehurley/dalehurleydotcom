#!/usr/bin/env node
/**
 * Blog Hero Image Generator
 *
 * Ports the Laravel ImageAgent service to a standalone Node.js script.
 * Uses the OpenAI API (gpt-image-1 + gpt-4o) to generate 1950s propaganda-
 * style hero images and thumbnails for blog posts, then writes the paths
 * back into the MDX frontmatter.
 *
 * Usage:
 *   OPENAI_API_KEY=sk-... node scripts/generate-images.mjs
 *   OPENAI_API_KEY=sk-... node scripts/generate-images.mjs --post=gpt5
 */

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import { createInterface } from 'readline';
import OpenAI from 'openai';
import sharp from 'sharp';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const ROOT = path.resolve(__dirname, '..');
const POSTS_DIR = path.join(ROOT, 'src', 'content', 'blog');
const IMAGES_DIR = path.join(ROOT, 'public', 'images');

// ---------------------------------------------------------------------------
// Prompt Engineering — direct port of ImageAgent::generateImagePrompt()
// ---------------------------------------------------------------------------

const characters = [
  'Confident brunette with vintage glasses', 'Athletic blonde with determined expression',
  'Elegant redhead with mysterious smile', 'Distinguished silver-haired professional',
  'Charismatic dark-haired innovator', 'Dynamic duo of tech partners',
  'Diverse team of three AI pioneers', 'Rugged masculine inventor',
  'Graceful feminine scientist', 'Non-binary tech visionary',
  'Scholarly professor with bow tie', 'Adventurous explorer with hiking gear',
  'Sophisticated businesswoman with briefcase', 'Young prodigy with creative energy',
  'Seasoned veteran with wise eyes', 'Energetic entrepreneur with bright smile',
  'Mysterious figure in lab coat', 'Charismatic leader addressing crowd',
  'Focused researcher examining data', 'Bold pioneer breaking barriers',
  'Innovative designer sketching concepts', 'Determined engineer solving problems',
  'Inspiring mentor guiding others', 'Creative artist blending mediums',
  'Strategic planner with vision board', 'Tech-savvy teenager with laptop',
  'Experienced craftsperson with tools', 'Passionate advocate speaking truth',
  'Dynamic athlete in training mode', 'Intellectual philosopher pondering deeply',
  'Practical farmer with innovative methods', 'Musical composer creating symphonies',
  'Medical professional saving lives', 'Space explorer reaching for stars',
  'Brilliant mathematician solving equations', 'Skilled chef creating culinary art',
  'Master architect designing futures', 'Dedicated teacher inspiring students',
  'Fearless journalist uncovering truth', 'Innovative startup founder disrupting markets',
  'Creative writer crafting stories', 'Wise elder sharing knowledge',
];

const outfits = [
  'lab coat and safety goggles', 'mechanic coveralls and tool belt',
  'pilot jacket and aviator glasses', 'chef apron and tall hat',
  'military uniform with medals', 'sharp business suit and briefcase',
  'nurse uniform and stethoscope', 'cowboy boots and ranch wear',
  'sailor outfit and captain hat', 'artist smock and beret',
  'detective coat and magnifying glass', 'astronaut suit and helmet',
  'teacher cardigan and glasses', 'construction hard hat and vest',
  'librarian sweater and cat-eye glasses', 'racing jumpsuit and helmet',
  'garden overalls and sun hat', 'firefighter gear and helmet',
  'police uniform and badge', 'doctor white coat and stethoscope',
  'scientist lab attire and clipboard', 'engineer hard hat and blueprints',
  'farmer denim overalls and straw hat', 'chef whites and neckerchief',
  'bartender vest and bow tie', 'barber striped shirt and suspenders',
  'postman uniform and mail bag', 'train conductor uniform and whistle',
  'circus performer sequined costume', 'magician tuxedo and top hat',
  'musician formal attire and bow tie', 'photographer vest with camera straps',
  'journalist trench coat and notebook', 'fashion designer measuring tape and pins',
  'hairdresser apron and styling tools', 'baker white uniform and flour dusting',
  'welder protective mask and torch', 'tailor measuring tape and pins',
  'martial arts gi and black belt', 'vintage swimwear and sunglasses',
  'lawyer pin-stripe suit and briefcase', 'banker formal attire and gold watch',
];

const settings = [
  'retro laboratory with bubbling beakers', 'vintage garage with classic cars',
  '1950s diner with neon signs', 'old-school barbershop with spinning pole',
  'classic library with towering shelves', 'antique observatory with telescope',
  'retro train station with steam engine', 'vintage market with fresh produce',
  'classic theater with spotlights', 'old factory with massive machinery',
  'retro submarine with periscopes', 'vintage airfield with propeller planes',
  'classic workshop with hand tools', 'old-timey circus with big top',
  'retro space station with control panels', 'art deco skyscraper with geometric patterns',
  'vintage record studio with analog equipment', 'classic soda fountain with chrome stools',
  'retro bowling alley with pin setters', 'old-fashioned pharmacy with medicine bottles',
  'vintage radio station with broadcasting booth', 'classic dance hall with mirror ball',
  'retro drive-in movie theater', 'old-timey newspaper office with printing press',
  'vintage television studio with cameras', 'classic ice cream parlor with checkered floor',
  'retro gas station with full service', 'old-school gymnasium with rope climbing',
  'vintage beauty salon with hair dryers', 'classic photography darkroom',
  'retro arcade with pinball machines', 'old-fashioned clockmaker shop',
  'vintage computer room with mainframes', 'classic science laboratory with equipment',
  'retro weather station with instruments', 'vintage switchboard room with operators',
  'classic control room with monitors', 'retro command center with radar screens',
  'old-timey war room with maps', 'vintage robot factory with assembly lines',
  'old-fashioned steampunk workshop', 'retro rocket launch pad with towers',
  'old-fashioned mission control', 'vintage space capsule interior',
  'classic alien laboratory with specimens', 'retro time machine chamber',
  'vintage brewery with vats', 'classic vineyard with grape vines',
  'retro greenhouse with tropical plants', 'vintage photography studio with lights',
  'classic art studio with canvases', 'retro carnival midway with games',
  'old-fashioned amusement park with rides', 'classic football stadium with lights',
  'retro ice skating rink with zamboni', 'vintage beach resort with umbrellas',
  'classic mountain retreat with views', 'retro desert spa with cacti',
  'vintage yacht club with sailboats', 'classic fishing lodge with tackle',
  'retro roadside diner with chrome', 'old-timey museum with exhibits',
  'vintage planetarium with dome', 'classic farmers market with produce',
  'retro craft fair with artisans', 'old-timey town square with gazebo',
];

const actions = [
  'operating complex machinery with precision', 'conducting scientific experiments with curiosity',
  'building architectural marvels with determination', 'performing on stage with passion',
  'racing vintage vehicles at breakneck speed', 'solving intricate puzzles with focused intensity',
  'teaching revolutionary concepts to eager students', 'exploring uncharted territories with courage',
  'creating artistic masterpieces with flowing creativity', 'leading innovative teams toward breakthrough solutions',
  'discovering hidden treasures in ancient ruins', 'inventing revolutionary devices in cluttered workshops',
  'debugging complex code with laser focus', 'analyzing data patterns on multiple screens',
  'assembling robotic components with steady hands', 'calibrating sensitive instruments with care',
  'welding metal structures with sparks flying', 'mixing chemical compounds in beakers',
  'sketching blueprints on large drafting tables', 'operating printing presses with rhythmic motion',
  'navigating spacecraft through asteroid fields', 'repairing vintage automobiles under the hood',
  'photographing specimens through microscopes', 'broadcasting live from radio stations',
  'performing surgery with surgical precision', 'cooking gourmet meals in bustling kitchens',
  'delivering passionate speeches to crowds', 'dancing energetically on polished floors',
  'playing musical instruments with soul', 'painting murals on expansive walls',
  'writing stories on vintage typewriters', 'studying stars through telescopes',
  'examining fossils with brushes', 'investigating crime scenes methodically',
  'negotiating business deals confidently', 'selling products with enthusiastic presentations',
  'bartending cocktails with flair', 'rescuing victims from dangerous situations',
  'providing first aid to injured persons', 'counseling troubled individuals compassionately',
  'demonstrating products effectively', 'training users on new systems',
  'repairing broken machinery expertly', 'installing new technology carefully',
  'testing functionality thoroughly', 'archiving records systematically',
  'leading meetings productively', 'mediating conflicts diplomatically',
  'mentoring newcomers generously', 'coaching performance improvements',
  'automating repetitive tasks intelligently', 'innovating green technologies',
  'breaking through limitations triumphantly', 'transforming industries boldly',
];

const colorPalettes = [
  'sunset orange, deep purple, and gold',
  'electric blue, hot pink, and silver',
  'forest green, burgundy, and copper',
  'coral, navy, and cream',
  'turquoise, rust, and ivory',
  'violet, amber, and charcoal',
  'crimson, sage, and bronze',
  'magenta, teal, and pearl',
  'ruby, mint, and graphite',
];

const props = [
  'vintage computers and calculators', 'classic tools and instruments',
  'retro vehicles and transportation', 'musical instruments and equipment',
  'scientific apparatus and gadgets', 'art supplies and creative tools',
  'vintage cameras and photography equipment', 'antique typewriters and printing machines',
  'classic laboratory beakers and test tubes', 'retro robots and mechanical automatons',
  'vintage medical equipment and stethoscopes', 'classic aviation instruments and propellers',
  'antique telescopes and navigational tools', 'retro gaming machines and pinball devices',
  'vintage industrial machinery and gears', 'classic electrical components and circuits',
  'antique clockwork mechanisms and springs', 'retro space age technology and satellites',
  'vintage farming tools and agricultural equipment', 'classic construction machinery and blueprints',
  'retro chemistry sets and periodic tables', 'vintage sewing machines and fabric tools',
  'classic woodworking tools and workbenches', 'retro electronic tubes and vacuum components',
  'vintage printing blocks and typography tools', 'classic diving equipment and oxygen tanks',
  'retro broadcasting equipment and microphones', 'vintage laboratory microscopes and slides',
  'classic fishing gear and tackle boxes', 'vintage jewelry making tools and gems',
  'classic watch repair instruments and gears', 'retro jukebox machines and record players',
  'vintage television sets and radio consoles', 'classic telephone booths and communication devices',
  'antique clocks and timing mechanisms', 'retro desk accessories and paperweights',
  'vintage fountain pens and writing instruments', 'classic drafting tools and architectural supplies',
  'antique measuring devices and rulers', 'retro power tools and workshop machinery',
  'vintage hand tools and hardware collections', 'classic gears and transmission components',
  'retro neon signs and advertising displays', 'vintage store fixtures and cash registers',
  'antique books and leather-bound volumes', 'retro magazines and newspaper headlines',
  'vintage maps and geographical charts', 'classic medals and military decorations',
];

const compositions = [
  'dynamic action shot with motion blur', 'intimate close-up with intense focus',
  'wide establishing shot with epic scope', 'symmetrical balanced composition',
  'asymmetrical dynamic arrangement', 'layered depth with foreground/background',
  'circular vortex composition', 'diagonal leading lines', 'triangular power pose',
];

function pick(arr) {
  return arr[Math.floor(Math.random() * arr.length)];
}

function buildMetaPrompt(post) {
  const character = pick(characters);
  const outfit = pick(outfits);
  const setting = pick(settings);
  const action = pick(actions);
  const colors = pick(colorPalettes);
  const propItem = pick(props);
  const composition = pick(compositions);

  const includeBanner = Math.random() < 0.5;
  const bannerText = includeBanner
    ? "Include an attention-grabbing banner with ≤ 8 words that captures the blog's essence. "
    : '';

  const wildcards = [];
  if (Math.random() < 0.3) wildcards.push('AI-powered robotic companion');
  if (Math.random() < 0.3) wildcards.push('dramatic weather effects');
  if (Math.random() < 0.3) wildcards.push('retro-futuristic technology blend');
  if (Math.random() < 0.3) wildcards.push('environmental storytelling details');
  const wildcardText = wildcards.length ? `Include: ${wildcards.join(', ')}. ` : '';

  return `You are an elite visual prompt engineer with a flair for mid-century advertising art. Create a SINGLE, production-ready prompt that an AI image generator (e.g. Midjourney, DALL·E) can use to produce a striking hero/featured image for the following blog post title: "${post.title}".

Use the following blog post content for inspiration:
<blog-post-content>
${(post.content || '').slice(0, 2000)}
</blog-post-content>

The finished artwork must feel like a 1950s comic-style propaganda/pin-up poster while representing Dale Hurley's brand promise—delivering innovative AI solutions and empowering businesses through cutting-edge technology.

### Creative Vision Framework

**Creative Framework for Maximum Variation:**

${bannerText}Create a ${composition} featuring an extremely good-looking ${character} wearing ${outfit} in a ${setting}, ${action}. Use color palette of ${colors}. Include ${propItem} as key visual elements. ${wildcardText}

**Art Direction:**
• Style: 1950s propaganda/pin-up poster/comic book advertisement with thick black outlines, vintage advertising/poster aesthetic, halftone shading, slight paper texture overlay.
• Mood: Confident empowerment, playful innovation, retro glamour meets high-tech revolution, explosive energy, empowering transformation, playful innovation, confident optimism.
• Aspect Ratio: Landscape (3:2) optimized for hero images
• Avoid: Generic corporate aesthetics, predictable compositions

**Creative Mandate:** This must feel completely unique.

Be creative and have fun with the concept and make sure in some way it reflects the blog post's content and Dale Hurley's brand identity as a leader in AI innovation. The image should be visually striking, memorable, and perfectly suited for a blog post header.

### Prompt Output Rules
• Start with a direct instruction to the generator (e.g. "Create a vibrant 1950s propaganda pin-up poster…")
• Include all stylistic directives above in concise, generator-friendly syntax
• **Do NOT wrap your prompt in back-ticks or markdown**
• Return ONLY the prompt—no commentary, pre-amble, or closing remarks
• Ensure composition works well in 3:2 aspect ratio
• Make sure the image captures the blog post essence with maximum visual impact and fun factor

Generate the image prompt now.`;
}

// ---------------------------------------------------------------------------
// Blog post parsing
// ---------------------------------------------------------------------------

function parseFrontmatter(content) {
  const match = content.match(/^---\n([\s\S]*?)\n---\n([\s\S]*)$/);
  if (!match) return { frontmatter: {}, body: content };

  const raw = match[1];
  const body = match[2];
  const frontmatter = {};

  // Simple YAML key: value parser (handles strings, arrays, quoted values)
  const lines = raw.split('\n');
  let i = 0;
  while (i < lines.length) {
    const line = lines[i];
    const keyMatch = line.match(/^(\w[\w-]*)\s*:\s*(.*)?$/);
    if (!keyMatch) { i++; continue; }
    const key = keyMatch[1];
    const val = (keyMatch[2] || '').trim();

    if (val === '' || val === '[') {
      // Collect array lines
      const arr = [];
      i++;
      while (i < lines.length && lines[i].trim().startsWith('-')) {
        arr.push(lines[i].trim().replace(/^-\s*/, '').replace(/['"]/g, ''));
        i++;
      }
      // Also handle inline array remainder
      if (val.includes('[') && !val.includes(']')) {
        // already handled above, skip
      }
      frontmatter[key] = arr;
    } else if (val.startsWith('[') && val.endsWith(']')) {
      frontmatter[key] = val.slice(1, -1).split(',').map(s => s.trim().replace(/['"]/g, ''));
      i++;
    } else {
      frontmatter[key] = val.replace(/^["']|["']$/g, '');
      i++;
    }
  }

  return { frontmatter, body };
}

function getAllPosts() {
  if (!fs.existsSync(POSTS_DIR)) {
    console.error('Posts directory not found:', POSTS_DIR);
    process.exit(1);
  }

  return fs.readdirSync(POSTS_DIR)
    .filter(name => fs.statSync(path.join(POSTS_DIR, name)).isDirectory())
    .map(slug => {
      const mdxPath = path.join(POSTS_DIR, slug, 'index.mdx');
      if (!fs.existsSync(mdxPath)) return null;
      const raw = fs.readFileSync(mdxPath, 'utf8');
      const { frontmatter, body } = parseFrontmatter(raw);
      return { slug, title: frontmatter.title || slug, content: body, frontmatter, mdxPath, raw };
    })
    .filter(Boolean);
}

// ---------------------------------------------------------------------------
// Image generation
// ---------------------------------------------------------------------------

async function generateImage(openai, post) {
  console.log(`\n→ Generating prompt for: ${post.title}`);

  const metaPrompt = buildMetaPrompt(post);

  // Step 1: Use GPT-4o to craft a polished image prompt
  const chatRes = await openai.chat.completions.create({
    model: 'gpt-4o',
    messages: [{ role: 'user', content: metaPrompt }],
  });
  const imagePrompt = chatRes.choices[0].message.content.trim();
  console.log('  Prompt:', imagePrompt.slice(0, 120) + '…');

  // Step 2: Generate the image
  console.log('  Calling image API…');
  const imgRes = await openai.images.generate({
    model: 'gpt-image-1',
    prompt: imagePrompt,
    size: '1536x1024',
    output_format: 'webp',
  });

  const b64 = imgRes.data[0].b64_json;
  const buffer = Buffer.from(b64, 'base64');

  // Step 3: Save hero image
  fs.mkdirSync(IMAGES_DIR, { recursive: true });
  const heroPath = path.join(IMAGES_DIR, `${post.slug}.webp`);
  const thumbPath = path.join(IMAGES_DIR, `${post.slug}-thumbnail.webp`);

  // Optimise and save hero (max 1536×1024, quality 82)
  await sharp(buffer)
    .resize(1536, 1024, { fit: 'cover', position: 'center' })
    .webp({ quality: 82 })
    .toFile(heroPath);

  // Step 4: Create thumbnail (384×256)
  await sharp(buffer)
    .resize(384, 256, { fit: 'cover', position: 'center' })
    .webp({ quality: 80 })
    .toFile(thumbPath);

  const heroSize = (fs.statSync(heroPath).size / 1024).toFixed(1);
  const thumbSize = (fs.statSync(thumbPath).size / 1024).toFixed(1);
  console.log(`  ✓ Hero: ${heroSize} KB  |  Thumb: ${thumbSize} KB`);

  return {
    imagePath: `images/${post.slug}.webp`,
    thumbnailPath: `images/${post.slug}-thumbnail.webp`,
  };
}

// ---------------------------------------------------------------------------
// MDX frontmatter updater
// ---------------------------------------------------------------------------

function updateFrontmatter(post, imagePath, thumbnailPath) {
  let updated = post.raw;

  // Replace or insert image field
  if (/^image:/m.test(updated)) {
    updated = updated.replace(/^image:.*$/m, `image: ${imagePath}`);
  } else {
    updated = updated.replace(/^---\n/, `---\nimage: ${imagePath}\n`);
  }

  if (/^thumbnail:/m.test(updated)) {
    updated = updated.replace(/^thumbnail:.*$/m, `thumbnail: ${thumbnailPath}`);
  } else {
    updated = updated.replace(/^---\n/, `---\nthumbnail: ${thumbnailPath}\n`);
  }

  fs.writeFileSync(post.mdxPath, updated, 'utf8');
  console.log(`  ✓ Updated frontmatter: ${post.mdxPath}`);
}

// ---------------------------------------------------------------------------
// Interactive CLI
// ---------------------------------------------------------------------------

function prompt(question) {
  const rl = createInterface({ input: process.stdin, output: process.stdout });
  return new Promise(resolve => rl.question(question, ans => { rl.close(); resolve(ans.trim()); }));
}

async function main() {
  const apiKey = process.env.OPENAI_API_KEY;
  if (!apiKey) {
    console.error('Error: OPENAI_API_KEY environment variable is required.');
    process.exit(1);
  }

  const openai = new OpenAI({ apiKey });
  const posts = getAllPosts();

  if (posts.length === 0) {
    console.log('No posts found in', POSTS_DIR);
    process.exit(0);
  }

  // --post=slug flag
  const postArg = process.argv.find(a => a.startsWith('--post='));
  if (postArg) {
    const slug = postArg.split('=')[1];
    const post = posts.find(p => p.slug === slug);
    if (!post) {
      console.error(`Post not found: ${slug}`);
      process.exit(1);
    }
    const { imagePath, thumbnailPath } = await generateImage(openai, post);
    updateFrontmatter(post, imagePath, thumbnailPath);
    console.log('\nDone!');
    return;
  }

  // Interactive menu
  console.log('\nBlog Hero Image Generator\n');
  posts.forEach((p, i) => console.log(`  ${i + 1}. ${p.slug} — ${p.title}`));
  console.log('\n  0. Generate images for ALL posts');

  const choice = await prompt('\nEnter number (or 0 for all): ');
  const num = parseInt(choice, 10);

  if (num === 0) {
    for (const post of posts) {
      try {
        const { imagePath, thumbnailPath } = await generateImage(openai, post);
        updateFrontmatter(post, imagePath, thumbnailPath);
      } catch (err) {
        console.error(`  ✗ Failed for ${post.slug}:`, err.message);
      }
    }
  } else if (num >= 1 && num <= posts.length) {
    const post = posts[num - 1];
    const { imagePath, thumbnailPath } = await generateImage(openai, post);
    updateFrontmatter(post, imagePath, thumbnailPath);
  } else {
    console.log('Invalid selection.');
    process.exit(1);
  }

  console.log('\nDone!');
}

main().catch(err => {
  console.error('Fatal error:', err);
  process.exit(1);
});

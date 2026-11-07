import * as THREE from 'three';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);
gsap.ticker.lagSmoothing(1000, 16);

/**
 * Shared PBR Material Factory (Cached to minimize GPU shader compilations)
 */
let sharedMaterials = null;

function getHeritageMaterials() {
    if (sharedMaterials) {
        return sharedMaterials;
    }

    sharedMaterials = {
        brassPolished: new THREE.MeshStandardMaterial({
            color: 0xd4af37,
            metalness: 0.82,
            roughness: 0.24,
            emissive: 0x3a2808,
            emissiveIntensity: 0.08,
        }),
        brassAged: new THREE.MeshStandardMaterial({
            color: 0xa67c52,
            metalness: 0.68,
            roughness: 0.36,
            emissive: 0x2a1a08,
            emissiveIntensity: 0.05,
        }),
        bronzeWarm: new THREE.MeshStandardMaterial({
            color: 0x6b3f1e,
            metalness: 0.58,
            roughness: 0.38,
            emissive: 0x221105,
            emissiveIntensity: 0.06,
        }),
        castIron: new THREE.MeshStandardMaterial({
            color: 0x2a211a,
            metalness: 0.52,
            roughness: 0.48,
        }),
        steelDark: new THREE.MeshStandardMaterial({
            color: 0x4a3f37,
            metalness: 0.66,
            roughness: 0.32,
        }),
        parchmentEnamel: new THREE.MeshStandardMaterial({
            color: 0xfaf6ee,
            metalness: 0.08,
            roughness: 0.72,
        }),
    };

    return sharedMaterials;
}

/**
 * Checks if WebGL is supported in the current browser environment
 */
function isWebGLAvailable() {
    try {
        const canvas = document.createElement('canvas');
        return !!(
            window.WebGLRenderingContext &&
            (canvas.getContext('webgl') || canvas.getContext('experimental-webgl'))
        );
    } catch {
        return false;
    }
}

/**
 * Builds the 1911 Steam Cane Mill & Flywheel 3D Assembly
 * Organized into 5 named mechanical sub-groups for GSAP Exploded View & Hotspot Inspection
 */
function build1911SteamMillAssembly() {
    const mats = getHeritageMaterials();
    const root = new THREE.Group();
    root.name = 'PGCandiBaru1911Assembly';

    // -------------------------------------------------------------------------
    // 1. CHASSIS & LORI RAIL BASE (chassisGroup)
    // -------------------------------------------------------------------------
    const chassisGroup = new THREE.Group();
    chassisGroup.name = 'chassisGroup';

    const basePlate = new THREE.Mesh(
        new THREE.BoxGeometry(4.2, 0.24, 2.1),
        mats.castIron
    );
    basePlate.position.set(0, -0.82, 0);
    chassisGroup.add(basePlate);

    const brassTrimPlinth = new THREE.Mesh(
        new THREE.BoxGeometry(4.32, 0.07, 2.22),
        mats.brassAged
    );
    brassTrimPlinth.position.set(0, -0.95, 0);
    chassisGroup.add(brassTrimPlinth);

    // Lori Rails beneath the base
    const railGeo = new THREE.BoxGeometry(4.8, 0.08, 0.1);
    const railFront = new THREE.Mesh(railGeo, mats.steelDark);
    railFront.position.set(0, -1.02, 0.85);
    const railBack = new THREE.Mesh(railGeo, mats.steelDark);
    railBack.position.set(0, -1.02, -0.85);
    chassisGroup.add(railFront, railBack);

    // Brass Anchor Bolts on corners
    const boltGeo = new THREE.CylinderGeometry(0.07, 0.07, 0.14, 6);
    const boltOffsets = [
        [-1.9, -0.68, 0.88],
        [1.9, -0.68, 0.88],
        [-1.9, -0.68, -0.88],
        [1.9, -0.68, -0.88],
        [0, -0.68, 0.88],
        [0, -0.68, -0.88],
    ];
    boltOffsets.forEach(([bx, by, bz]) => {
        const bolt = new THREE.Mesh(boltGeo, mats.brassPolished);
        bolt.position.set(bx, by, bz);
        chassisGroup.add(bolt);
    });

    chassisGroup.userData.homePos = new THREE.Vector3(0, 0, 0);
    chassisGroup.userData.explodedPos = new THREE.Vector3(0, -0.55, 0);
    root.add(chassisGroup);

    // -------------------------------------------------------------------------
    // 2. 1911 COLONIAL STEAM FLYWHEEL ASSEMBLY (flywheelGroup)
    // -------------------------------------------------------------------------
    const flywheelGroup = new THREE.Group();
    flywheelGroup.name = 'flywheelGroup';
    flywheelGroup.position.set(-1.05, 0.22, 0);

    // Pedestal A-frame bearings
    const pillarGeo = new THREE.BoxGeometry(0.24, 1.15, 0.26);
    const pillarFront = new THREE.Mesh(pillarGeo, mats.castIron);
    pillarFront.position.set(0, -0.45, 0.42);
    const pillarBack = new THREE.Mesh(pillarGeo, mats.castIron);
    pillarBack.position.set(0, -0.45, -0.42);
    flywheelGroup.add(pillarFront, pillarBack);

    // Rotating wheel sub-group
    const wheelSpinGroup = new THREE.Group();
    wheelSpinGroup.name = 'wheelSpinGroup';

    // Outer heavy rim
    const outerRim = new THREE.Mesh(
        new THREE.TorusGeometry(1.05, 0.12, 16, 48),
        mats.bronzeWarm.clone()
    );
    wheelSpinGroup.add(outerRim);

    // Brass precision ring
    const brassRing = new THREE.Mesh(
        new THREE.TorusGeometry(0.92, 0.035, 12, 48),
        mats.brassPolished.clone()
    );
    brassRing.position.z = 0.08;
    const brassRingBack = brassRing.clone();
    brassRingBack.position.z = -0.08;
    wheelSpinGroup.add(brassRing, brassRingBack);

    // Center Hub & Axle
    const hub = new THREE.Mesh(
        new THREE.CylinderGeometry(0.26, 0.26, 0.36, 20),
        mats.brassPolished
    );
    hub.rotation.x = Math.PI / 2;
    wheelSpinGroup.add(hub);

    const axle = new THREE.Mesh(
        new THREE.CylinderGeometry(0.11, 0.11, 1.15, 16),
        mats.steelDark
    );
    axle.rotation.x = Math.PI / 2;
    wheelSpinGroup.add(axle);

    // 8 Radial Tapered Spokes
    const spokeGeo = new THREE.CylinderGeometry(0.042, 0.062, 1.92, 10);
    for (let i = 0; i < 4; i++) {
        const spoke = new THREE.Mesh(spokeGeo, mats.brassAged);
        spoke.rotation.z = (i * Math.PI) / 4;
        wheelSpinGroup.add(spoke);
    }

    // Crank pin on wheel face
    const crankPin = new THREE.Mesh(
        new THREE.CylinderGeometry(0.065, 0.065, 0.28, 12),
        mats.brassPolished
    );
    crankPin.rotation.x = Math.PI / 2;
    crankPin.position.set(0.52, 0, 0.22);
    wheelSpinGroup.add(crankPin);

    flywheelGroup.add(wheelSpinGroup);
    flywheelGroup.userData.homePos = flywheelGroup.position.clone();
    flywheelGroup.userData.explodedPos = new THREE.Vector3(-1.75, 0.48, 0.55);
    root.add(flywheelGroup);

    // -------------------------------------------------------------------------
    // 3. RECIPROCATING STEAM PISTON & CYLINDER (pistonGroup)
    // -------------------------------------------------------------------------
    const pistonGroup = new THREE.Group();
    pistonGroup.name = 'pistonGroup';
    pistonGroup.position.set(0.25, -0.18, 0.36);

    // Horizontal Steam Boiler Cylinder
    const steamCylinder = new THREE.Mesh(
        new THREE.CylinderGeometry(0.34, 0.34, 1.15, 24),
        mats.bronzeWarm.clone()
    );
    steamCylinder.rotation.z = Math.PI / 2;
    steamCylinder.position.set(0.35, 0, 0);
    pistonGroup.add(steamCylinder);

    // Brass Flanges on Steam Cylinder
    const flangeGeo = new THREE.CylinderGeometry(0.4, 0.4, 0.08, 24);
    const flangeLeft = new THREE.Mesh(flangeGeo, mats.brassPolished);
    flangeLeft.rotation.z = Math.PI / 2;
    flangeLeft.position.set(-0.2, 0, 0);
    const flangeRight = new THREE.Mesh(flangeGeo, mats.brassPolished);
    flangeRight.rotation.z = Math.PI / 2;
    flangeRight.position.set(0.9, 0, 0);
    pistonGroup.add(flangeLeft, flangeRight);

    // Moving Piston Rod Sub-Group
    const pistonRodMover = new THREE.Group();
    pistonRodMover.name = 'pistonRodMover';
    const rodMesh = new THREE.Mesh(
        new THREE.CylinderGeometry(0.065, 0.065, 1.35, 14),
        mats.brassPolished
    );
    rodMesh.rotation.z = Math.PI / 2;
    rodMesh.position.set(-0.62, 0, 0);

    const crossheadBlock = new THREE.Mesh(
        new THREE.BoxGeometry(0.26, 0.22, 0.24),
        mats.castIron
    );
    crossheadBlock.position.set(-1.12, 0, 0);
    pistonRodMover.add(rodMesh, crossheadBlock);

    pistonGroup.add(pistonRodMover);
    pistonGroup.userData.homePos = pistonGroup.position.clone();
    pistonGroup.userData.explodedPos = new THREE.Vector3(0.25, -0.25, 1.15);
    root.add(pistonGroup);

    // -------------------------------------------------------------------------
    // 4. TRIPLE SUGAR CANE CRUSHER ROLLERS & REDUCTION GEARS (crusherGroup)
    // -------------------------------------------------------------------------
    const crusherGroup = new THREE.Group();
    crusherGroup.name = 'crusherGroup';
    crusherGroup.position.set(1.15, -0.05, -0.22);

    // Side Housing Frames
    const housingGeo = new THREE.BoxGeometry(1.15, 1.1, 0.14);
    const housingFront = new THREE.Mesh(housingGeo, mats.castIron);
    housingFront.position.set(0, -0.15, 0.55);
    const housingBack = new THREE.Mesh(housingGeo, mats.castIron);
    housingBack.position.set(0, -0.15, -0.55);
    crusherGroup.add(housingFront, housingBack);

    // 3 Crusher Rollers (Top King Roller + 2 Lower Cane/Bagasse Rollers)
    const rollerGeo = new THREE.CylinderGeometry(0.26, 0.26, 0.96, 20);
    const rollerRingGeo = new THREE.TorusGeometry(0.265, 0.02, 8, 24);

    function createGroovedRoller(mat) {
        const rGroup = new THREE.Group();
        const cyl = new THREE.Mesh(rollerGeo, mat);
        cyl.rotation.x = Math.PI / 2;
        rGroup.add(cyl);

        for (let z = -0.36; z <= 0.36; z += 0.18) {
            const groove = new THREE.Mesh(rollerRingGeo, mats.brassPolished);
            groove.position.z = z;
            rGroup.add(groove);
        }
        return rGroup;
    }

    const topRoller = createGroovedRoller(mats.bronzeWarm.clone());
    topRoller.position.set(0, 0.22, 0);
    const frontRoller = createGroovedRoller(mats.steelDark);
    frontRoller.position.set(-0.32, -0.26, 0);
    const backRoller = createGroovedRoller(mats.steelDark);
    backRoller.position.set(0.32, -0.26, 0);
    crusherGroup.add(topRoller, frontRoller, backRoller);

    // External Crown Reduction Gear
    const crownGear = new THREE.Group();
    crownGear.position.set(0, 0.22, -0.72);
    const gearDisc = new THREE.Mesh(
        new THREE.CylinderGeometry(0.46, 0.46, 0.14, 24),
        mats.brassAged.clone()
    );
    gearDisc.rotation.x = Math.PI / 2;
    crownGear.add(gearDisc);

    const toothGeo = new THREE.BoxGeometry(0.08, 0.12, 0.16);
    for (let t = 0; t < 12; t++) {
        const angle = (t * Math.PI * 2) / 12;
        const tooth = new THREE.Mesh(toothGeo, mats.brassPolished);
        tooth.position.set(Math.cos(angle) * 0.48, Math.sin(angle) * 0.48, 0);
        tooth.rotation.z = angle;
        crownGear.add(tooth);
    }
    crusherGroup.add(crownGear);

    crusherGroup.userData.homePos = crusherGroup.position.clone();
    crusherGroup.userData.explodedPos = new THREE.Vector3(1.88, 0.15, -0.68);
    root.add(crusherGroup);

    // -------------------------------------------------------------------------
    // 5. OVERHEAD BRASS STEAM MANIFOLD & CENTRIFUGAL GOVERNOR (pipeGroup)
    // -------------------------------------------------------------------------
    const pipeGroup = new THREE.Group();
    pipeGroup.name = 'pipeGroup';
    pipeGroup.position.set(0.1, 0.72, 0);

    // Main Horizontal Overhead Steam Pipe
    const mainPipe = new THREE.Mesh(
        new THREE.CylinderGeometry(0.085, 0.085, 2.35, 16),
        mats.brassPolished.clone()
    );
    mainPipe.rotation.z = Math.PI / 2;
    pipeGroup.add(mainPipe);

    // Vertical Riser Pipe to Boiler Cylinder
    const riserPipe = new THREE.Mesh(
        new THREE.CylinderGeometry(0.075, 0.075, 0.85, 16),
        mats.brassAged
    );
    riserPipe.position.set(0.5, -0.42, 0.36);
    pipeGroup.add(riserPipe);

    // Watt Centrifugal Governor Tower (Spinning Brass Balls)
    const governorSpinner = new THREE.Group();
    governorSpinner.name = 'governorSpinner';
    governorSpinner.position.set(-0.45, 0.35, 0);

    const govSpindle = new THREE.Mesh(
        new THREE.CylinderGeometry(0.04, 0.04, 0.75, 12),
        mats.steelDark
    );
    governorSpinner.add(govSpindle);

    const ballGeo = new THREE.SphereGeometry(0.11, 16, 16);
    const ballLeft = new THREE.Mesh(ballGeo, mats.brassPolished);
    ballLeft.position.set(-0.24, 0.08, 0);
    const ballRight = new THREE.Mesh(ballGeo, mats.brassPolished);
    ballRight.position.set(0.24, 0.08, 0);

    const armGeo = new THREE.CylinderGeometry(0.02, 0.02, 0.36, 8);
    const armLeft = new THREE.Mesh(armGeo, mats.brassAged);
    armLeft.position.set(-0.12, 0.2, 0);
    armLeft.rotation.z = -0.65;
    const armRight = new THREE.Mesh(armGeo, mats.brassAged);
    armRight.position.set(0.12, 0.2, 0);
    armRight.rotation.z = 0.65;

    governorSpinner.add(ballLeft, ballRight, armLeft, armRight);
    pipeGroup.add(governorSpinner);

    // Vintage Pressure Gauge Dial
    const gaugeBody = new THREE.Mesh(
        new THREE.CylinderGeometry(0.18, 0.18, 0.08, 20),
        mats.brassPolished
    );
    gaugeBody.rotation.x = Math.PI / 2;
    gaugeBody.position.set(0.5, 0.28, 0.12);
    const gaugeFace = new THREE.Mesh(
        new THREE.CylinderGeometry(0.14, 0.14, 0.09, 20),
        mats.parchmentEnamel
    );
    gaugeFace.rotation.x = Math.PI / 2;
    gaugeFace.position.set(0.5, 0.28, 0.125);
    pipeGroup.add(gaugeBody, gaugeFace);

    pipeGroup.userData.homePos = pipeGroup.position.clone();
    pipeGroup.userData.explodedPos = new THREE.Vector3(0.1, 1.38, 0.25);
    root.add(pipeGroup);

    // Store animated sub-references for the unified ticker
    root.userData = {
        parts: [chassisGroup, flywheelGroup, pistonGroup, crusherGroup, pipeGroup],
        wheelSpinGroup,
        pistonRodMover,
        topRoller,
        frontRoller,
        backRoller,
        crownGear,
        governorSpinner,
        highlightMap: {
            flywheel: [outerRim, brassRing],
            crusher: [topRoller.children[0], gearDisc],
            pipes: [mainPipe, steamCylinder],
        },
    };

    return root;
}

/**
 * Builds the 3D Levitating Brass Heritage Seal Medallion (for Boarding Pass & E-Ticket)
 */
function build1911BrassSealAssembly() {
    const mats = getHeritageMaterials();
    const root = new THREE.Group();
    root.name = 'BrassSeal1911';

    // Outer Notched Gear Coin Rim
    const coinBase = new THREE.Mesh(
        new THREE.CylinderGeometry(1.18, 1.18, 0.18, 32),
        mats.brassPolished.clone()
    );
    coinBase.rotation.x = Math.PI / 2;
    root.add(coinBase);

    // 24 Gear Teeth around Medallion Perimeter
    const notchGeo = new THREE.BoxGeometry(0.12, 0.14, 0.2);
    for (let i = 0; i < 24; i++) {
        const angle = (i * Math.PI * 2) / 24;
        const notch = new THREE.Mesh(notchGeo, mats.brassAged);
        notch.position.set(Math.cos(angle) * 1.22, Math.sin(angle) * 1.22, 0);
        notch.rotation.z = angle;
        root.add(notch);
    }

    // Recessed Dark Bronze Inner Field (Front & Back)
    const innerField = new THREE.Mesh(
        new THREE.CylinderGeometry(0.95, 0.95, 0.2, 32),
        mats.bronzeWarm
    );
    innerField.rotation.x = Math.PI / 2;
    root.add(innerField);

    // Raised Inner Flywheel Emblem Relief
    const reliefRing = new THREE.Mesh(
        new THREE.TorusGeometry(0.62, 0.07, 12, 32),
        mats.brassPolished
    );
    reliefRing.position.z = 0.11;
    const reliefRingBack = reliefRing.clone();
    reliefRingBack.position.z = -0.11;
    root.add(reliefRing, reliefRingBack);

    const spokeGeo = new THREE.BoxGeometry(1.18, 0.07, 0.24);
    for (let s = 0; s < 3; s++) {
        const spoke = new THREE.Mesh(spokeGeo, mats.brassPolished);
        spoke.rotation.z = (s * Math.PI) / 3;
        root.add(spoke);
    }

    const centerJewel = new THREE.Mesh(
        new THREE.CylinderGeometry(0.22, 0.22, 0.28, 16),
        mats.castIron
    );
    centerJewel.rotation.x = Math.PI / 2;
    root.add(centerJewel);

    const centerPin = new THREE.Mesh(
        new THREE.CylinderGeometry(0.11, 0.11, 0.32, 16),
        mats.brassPolished
    );
    centerPin.rotation.x = Math.PI / 2;
    root.add(centerPin);

    return root;
}

/**
 * Global Registry of Active 3D Viewer Instances driven by a Single Unified GSAP Ticker
 */
const activeViewers = new Set();
let tickerRegistered = false;

function registerUnifiedTicker() {
    if (tickerRegistered) {
        return;
    }
    tickerRegistered = true;

    gsap.ticker.add((time, deltaTime) => {
        const dt = Math.min(deltaTime * 0.001, 0.05);

        activeViewers.forEach((viewer) => {
            if (!viewer.isVisible) {
                return;
            }
            viewer.update(time, dt);
        });
    });
}

/**
 * Initializes a single `[data-3d-viewer]` container with Three.js + GLTFLoader + GSAP
 */
function createHeritageViewer(container) {
    const mode = container.getAttribute('data-3d-viewer') || 'mill-engine';
    const externalGlbUrl = container.getAttribute('data-model-url') || '';
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const scene = new THREE.Scene();

    // Camera configuration per mode
    const isSeal = mode === 'brass-seal';
    const fov = isSeal ? 36 : 38;
    const width = Math.max(container.clientWidth, 120);
    const height = Math.max(container.clientHeight, 120);

    const camera = new THREE.PerspectiveCamera(fov, width / height, 0.1, 100);
    const defaultCamPos = isSeal
        ? new THREE.Vector3(0, 0, 4.1)
        : new THREE.Vector3(0.25, 1.15, 4.65);
    const defaultLookAt = isSeal
        ? new THREE.Vector3(0, 0, 0)
        : new THREE.Vector3(0, -0.05, 0);

    camera.position.copy(defaultCamPos);
    const lookAtTarget = defaultLookAt.clone();
    camera.lookAt(lookAtTarget);

    // WebGLRenderer with DPR Capping (max 1.75) for smooth 60fps on Retina/4K screens
    const renderer = new THREE.WebGLRenderer({
        antialias: true,
        alpha: true,
        powerPreference: 'high-performance',
    });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 1.75));
    renderer.setSize(width, height);
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.15;

    renderer.domElement.className = 'heritage-webgl-canvas w-full h-full block select-none';
    renderer.domElement.setAttribute('aria-hidden', 'true');
    container.appendChild(renderer.domElement);

    // Warm Heritage Studio Lighting (Brass Key Light + Parchment Fill + Rim Light)
    const ambientLight = new THREE.AmbientLight(0xfaf6ee, 1.35);
    scene.add(ambientLight);

    const keyLight = new THREE.DirectionalLight(0xffe6b8, 2.6);
    keyLight.position.set(4.5, 6.0, 5.0);
    scene.add(keyLight);

    const fillLight = new THREE.DirectionalLight(0xc8a24a, 1.25);
    fillLight.position.set(-4.5, 2.0, 3.0);
    scene.add(fillLight);

    const rimLight = new THREE.PointLight(0xd4af37, 2.2, 12);
    rimLight.position.set(0, 2.5, -3.2);
    scene.add(rimLight);

    // Pivot Group for Mouse Parallax & Drag Orbit
    const pivotGroup = new THREE.Group();
    scene.add(pivotGroup);

    const assembly = isSeal
        ? build1911BrassSealAssembly()
        : build1911SteamMillAssembly();
    pivotGroup.add(assembly);

    // Optional External .glb / .gltf Model Loader (Replaces procedural mesh if provided & valid)
    if (externalGlbUrl) {
        const loader = new GLTFLoader();
        loader.load(
            externalGlbUrl,
            (gltf) => {
                const loadedModel = gltf.scene;
                const box = new THREE.Box3().setFromObject(loadedModel);
                const center = box.getCenter(new THREE.Vector3());
                const size = box.getSize(new THREE.Vector3());
                const maxDim = Math.max(size.x, size.y, size.z, 0.001);
                const scaleFactor = (isSeal ? 2.1 : 3.4) / maxDim;

                loadedModel.scale.setScalar(scaleFactor);
                loadedModel.position.sub(center.multiplyScalar(scaleFactor));

                pivotGroup.clear();
                pivotGroup.add(loadedModel);
            },
            undefined,
            () => {
                // Keep procedural 1911 Heritage assembly on error or missing file
            }
        );
    }

    // GSAP QuickTo Controllers for Zero-Allocation Cursor Parallax & Drag Orbit
    const quickRotY = gsap.quickTo(pivotGroup.rotation, 'y', {
        duration: 0.65,
        ease: 'power3.out',
    });
    const quickRotX = gsap.quickTo(pivotGroup.rotation, 'x', {
        duration: 0.65,
        ease: 'power3.out',
    });
    const quickLightX = gsap.quickTo(keyLight.position, 'x', {
        duration: 0.5,
        ease: 'power2.out',
    });

    let baseYaw = isSeal ? 0.25 : -0.32;
    let basePitch = isSeal ? 0.08 : 0.08;
    pivotGroup.rotation.set(basePitch, baseYaw, 0);

    let isDragging = false;
    let prevPointerX = 0;
    let prevPointerY = 0;
    let speedMultiplier = 1.0;
    let isExploded = false;
    let mechanicalPhase = 0;

    // Pointer Drag Orbit + Cursor Parallax
    container.addEventListener('pointerdown', (e) => {
        if (e.target.closest('button, a')) {
            return;
        }
        isDragging = true;
        prevPointerX = e.clientX;
        prevPointerY = e.clientY;
        container.classList.add('cursor-grabbing');
    });

    window.addEventListener(
        'pointerup',
        () => {
            if (isDragging) {
                isDragging = false;
                container.classList.remove('cursor-grabbing');
            }
        },
        { passive: true }
    );

    container.addEventListener(
        'pointermove',
        (e) => {
            const rect = container.getBoundingClientRect();
            if (!rect.width || !rect.height) {
                return;
            }

            const nx = ((e.clientX - rect.left) / rect.width - 0.5) * 2;
            const ny = ((e.clientY - rect.top) / rect.height - 0.5) * 2;

            if (isDragging) {
                const dx = e.clientX - prevPointerX;
                const dy = e.clientY - prevPointerY;
                prevPointerX = e.clientX;
                prevPointerY = e.clientY;

                baseYaw += dx * 0.011;
                basePitch = Math.max(-0.55, Math.min(0.65, basePitch + dy * 0.008));
                quickRotY(baseYaw);
                quickRotX(basePitch);
            } else if (!reducedMotion) {
                quickRotY(baseYaw + nx * (isSeal ? 0.45 : 0.24));
                quickRotX(basePitch + ny * (isSeal ? 0.32 : 0.15));
                quickLightX(4.5 + nx * 3.0);
            }
        },
        { passive: true }
    );

    container.addEventListener(
        'pointerleave',
        () => {
            if (!isDragging && !reducedMotion) {
                quickRotY(baseYaw);
                quickRotX(basePitch);
                quickLightX(4.5);
            }
        },
        { passive: true }
    );

    // ScrollTrigger Scrubbing for 3D Camera & Flywheel Velocity (Hero & Profile Artifact)
    if (!isSeal && !reducedMotion) {
        const stateProxy = { scrollBoost: 0 };
        ScrollTrigger.create({
            trigger: container,
            start: 'top bottom',
            end: 'bottom top',
            scrub: 1.1,
            onUpdate: (self) => {
                const progress = self.progress;
                baseYaw = -0.42 + progress * 0.68;
                quickRotY(baseYaw);
                speedMultiplier = 1.0 + Math.sin(progress * Math.PI) * 1.4;
                stateProxy.scrollBoost = progress;
            },
        });
    }

    // Viewer Instance Object
    const viewer = {
        container,
        mode,
        scene,
        camera,
        renderer,
        pivotGroup,
        assembly,
        lookAtTarget,
        defaultCamPos,
        defaultLookAt,
        isVisible: false,

        /**
         * Frame Update called by Unified `gsap.ticker`
         */
        update(time, dt) {
            if (!reducedMotion) {
                if (isSeal) {
                    mechanicalPhase += dt * 1.4;
                    assembly.position.y = Math.sin(mechanicalPhase * 1.6) * 0.08;
                    assembly.rotation.z = Math.sin(mechanicalPhase * 0.9) * 0.05;
                } else if (assembly.userData && assembly.userData.wheelSpinGroup) {
                    mechanicalPhase += dt * 1.85 * speedMultiplier;
                    const ud = assembly.userData;

                    // 1. Primary 1911 Steam Flywheel rotation
                    ud.wheelSpinGroup.rotation.z = -mechanicalPhase;

                    // 2. Reciprocating Piston Rod harmonic motion linked to flywheel phase
                    ud.pistonRodMover.position.x = Math.cos(mechanicalPhase) * 0.22;

                    // 3. Triple Cane Crusher Rollers counter-rotation
                    ud.topRoller.rotation.z = mechanicalPhase * 0.65;
                    ud.frontRoller.rotation.z = -mechanicalPhase * 0.65;
                    ud.backRoller.rotation.z = -mechanicalPhase * 0.65;
                    ud.crownGear.rotation.z = mechanicalPhase * 0.65;

                    // 4. Watt Centrifugal Governor high-speed spin
                    ud.governorSpinner.rotation.y = mechanicalPhase * 2.6;
                }
            }

            camera.lookAt(lookAtTarget);
            renderer.render(scene, camera);
        },

        /**
         * GSAP Timeline: Explodes or Reassembles the 5 3D Mechanical Sub-Assemblies
         */
        toggleExploded(forceState) {
            if (!assembly.userData || !assembly.userData.parts) {
                return false;
            }
            isExploded = typeof forceState === 'boolean' ? forceState : !isExploded;

            const parts = assembly.userData.parts;
            const tl = gsap.timeline({
                defaults: { duration: 1.1, ease: 'expo.inOut', overwrite: 'auto' },
            });

            parts.forEach((part, idx) => {
                const targetVec = isExploded ? part.userData.explodedPos : part.userData.homePos;
                tl.to(
                    part.position,
                    {
                        x: targetVec.x,
                        y: targetVec.y,
                        z: targetVec.z,
                    },
                    idx * 0.06
                );
            });

            // Simultaneously adjust camera distance for full exploded inspection
            tl.to(
                camera.position,
                {
                    x: isExploded ? 0.55 : defaultCamPos.x,
                    y: isExploded ? 1.55 : defaultCamPos.y,
                    z: isExploded ? 5.65 : defaultCamPos.z,
                },
                0
            );

            return isExploded;
        },

        /**
         * GSAP Timeline: Flies Camera to a Specific 3D Mechanical Hotspot
         */
        focusHotspot(hotspotKey) {
            const presets = {
                overview: {
                    cam: [defaultCamPos.x, defaultCamPos.y, defaultCamPos.z],
                    target: [defaultLookAt.x, defaultLookAt.y, defaultLookAt.z],
                    yaw: -0.32,
                },
                flywheel: {
                    cam: [-1.45, 0.65, 2.85],
                    target: [-1.05, 0.2, 0],
                    yaw: 0.28,
                },
                crusher: {
                    cam: [1.55, 0.55, 2.75],
                    target: [1.1, -0.05, -0.15],
                    yaw: -0.58,
                },
                pipes: {
                    cam: [0.15, 1.45, 2.85],
                    target: [0.1, 0.55, 0.15],
                    yaw: -0.12,
                },
            };

            const chosen = presets[hotspotKey] || presets.overview;
            baseYaw = chosen.yaw;

            const tl = gsap.timeline({
                defaults: { duration: 1.15, ease: 'power3.inOut', overwrite: 'auto' },
            });

            tl.to(
                camera.position,
                {
                    x: chosen.cam[0],
                    y: chosen.cam[1],
                    z: chosen.cam[2],
                },
                0
            );

            tl.to(
                lookAtTarget,
                {
                    x: chosen.target[0],
                    y: chosen.target[1],
                    z: chosen.target[2],
                },
                0
            );

            tl.to(
                pivotGroup.rotation,
                {
                    y: chosen.yaw,
                    x: 0.08,
                },
                0
            );

            // Pulse brass emissive highlight on the inspected part
            const highlightMap = assembly.userData?.highlightMap || {};
            Object.entries(highlightMap).forEach(([key, meshes]) => {
                const isTarget = key === hotspotKey;
                meshes.forEach((mesh) => {
                    if (mesh && mesh.material && 'emissiveIntensity' in mesh.material) {
                        gsap.to(mesh.material, {
                            emissiveIntensity: isTarget ? 0.55 : 0.06,
                            duration: 0.6,
                            ease: 'back.out(2)',
                        });
                    }
                });
            });
        },

        /**
         * GSAP Elastic Spin for the 3D Brass Seal Token
         */
        pulseSeal() {
            gsap.fromTo(
                pivotGroup.scale,
                { x: 0.86, y: 0.86, z: 0.86 },
                {
                    x: 1,
                    y: 1,
                    z: 1,
                    duration: 0.85,
                    ease: 'elastic.out(1.1, 0.45)',
                    overwrite: 'auto',
                }
            );
            baseYaw += Math.PI * 2;
            gsap.to(pivotGroup.rotation, {
                y: baseYaw,
                duration: 0.95,
                ease: 'back.out(1.4)',
                overwrite: 'auto',
            });
        },

        /**
         * Responsive Resize Handler
         */
        resize() {
            const w = Math.max(container.clientWidth, 80);
            const h = Math.max(container.clientHeight, 80);
            camera.aspect = w / h;
            camera.updateProjectionMatrix();
            renderer.setSize(w, h);
            if (!this.isVisible) {
                renderer.render(scene, camera);
            }
        },
    };

    // Click on Brass Seal spins it with GSAP elastic physics
    if (isSeal) {
        container.addEventListener('click', () => {
            viewer.pulseSeal();
        });
    }

    // Initial single frame render so canvas is never blank
    renderer.render(scene, camera);

    return viewer;
}

/**
 * Bootstraps all `[data-3d-viewer]` containers on the page with IntersectionObserver
 * and binds interactive GSAP controls (Exploded View, Camera Hotspots, Ticket Qty Spin).
 */
export function initHeritage3DScenes() {
    const containers = document.querySelectorAll('[data-3d-viewer]');
    if (!containers.length || !isWebGLAvailable()) {
        return new Map();
    }

    registerUnifiedTicker();

    const viewerMap = new Map();

    // IntersectionObserver pauses WebGL rendering when canvas is scrolled offscreen (0% idle GPU)
    const visibilityObserver =
        'IntersectionObserver' in window
            ? new IntersectionObserver(
                  (entries) => {
                      entries.forEach((entry) => {
                          const v = viewerMap.get(entry.target);
                          if (v) {
                              v.isVisible = entry.isIntersecting;
                          }
                      });
                  },
                  { rootMargin: '120px 0px', threshold: 0.01 }
              )
            : null;

    containers.forEach((container) => {
        const viewer = createHeritageViewer(container);
        activeViewers.add(viewer);
        viewerMap.set(container, viewer);

        if (container.id) {
            viewerMap.set(container.id, viewer);
        }

        if (visibilityObserver) {
            visibilityObserver.observe(container);
        } else {
            viewer.isVisible = true;
        }
    });

    // Bind Reset 3D Camera Buttons (`[data-3d-reset]`)
    document.querySelectorAll('[data-3d-reset]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-3d-reset');
            const viewer = viewerMap.get(targetId);
            if (viewer) {
                viewer.toggleExploded(false);
                viewer.focusHotspot('overview');
            }
        });
    });

    // Bind 3D Camera Hotspot Buttons (`[data-3d-hotspot]`)
    document.querySelectorAll('[data-3d-hotspot]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-3d-target');
            const hotspotKey = btn.getAttribute('data-3d-hotspot') || 'overview';
            const viewer = viewerMap.get(targetId);

            if (viewer) {
                viewer.focusHotspot(hotspotKey);
            }

            // Update active visual state on sibling hotspot buttons
            const group = btn.closest('[data-3d-hotspot-group]');
            if (group) {
                group.querySelectorAll('[data-3d-hotspot]').forEach((peer) => {
                    const active = peer === btn;
                    peer.setAttribute('aria-pressed', active ? 'true' : 'false');
                    peer.classList.toggle('bg-[#6B3F1E]', active);
                    peer.classList.toggle('text-[#FAF6EE]', active);
                    peer.classList.toggle('border-[#6B3F1E]', active);
                    peer.classList.toggle('bg-[#FFFFFF]', !active);
                    peer.classList.toggle('text-[#2A211A]', !active);
                });
            }
        });
    });

    // Bind Booking Wizard Ticket Quantity input (`#ticket_qty`) to spin the 3D Brass Seal
    const qtyInput = document.getElementById('ticket_qty');
    if (qtyInput) {
        qtyInput.addEventListener('input', () => {
            activeViewers.forEach((v) => {
                if (v.mode === 'brass-seal') {
                    v.pulseSeal();
                }
            });
        });
    }

    // Debounced Window Resize
    let resizeTimer = null;
    window.addEventListener(
        'resize',
        () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                activeViewers.forEach((v) => v.resize());
                ScrollTrigger.refresh();
            }, 120);
        },
        { passive: true }
    );

    return viewerMap;
}


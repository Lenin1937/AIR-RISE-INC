<template>
    <div ref="containerRef" class="absolute inset-0 w-full h-full overflow-hidden">
        <!-- Animated Particles Canvas -->
        <canvas ref="particlesCanvas" class="absolute inset-0 w-full h-full pointer-events-none" style="z-index: 5;"></canvas>
        
        <!-- CORPIUS Logo Overlay - Positioned on Right Side -->
        <div class="absolute inset-0 flex items-center justify-end pr-4 lg:pr-20 pointer-events-none z-10">
            <div class="text-right">
                <div class="text-yellow-400 font-bold text-4xl sm:text-5xl lg:text-7xl xl:text-8xl mb-2 drop-shadow-2xl" 
                     style="text-shadow: 0 0 40px rgba(250, 204, 21, 0.6), 0 0 80px rgba(250, 204, 21, 0.3);">
                    CORPIUS
                </div>
                <div class="text-white text-xs sm:text-sm lg:text-lg tracking-widest opacity-90 font-light">
                    GLOBAL BUSINESS SOLUTIONS
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import * as THREE from 'three';
import { OrbitControls } from 'three-stdlib';
import { onMounted, onUnmounted, ref } from 'vue';

const containerRef = ref(null);
const particlesCanvas = ref(null);
let scene, camera, renderer, controls;
let earthGroup, earthMesh, lightsMesh, cloudsMesh, glowMesh, stars;
let animationId;
let particleAnimationId;

const initGlobe = () => {
    if (!containerRef.value) return;

    const width = containerRef.value.offsetWidth;
    const height = containerRef.value.offsetHeight;

    scene = new THREE.Scene();
    camera = new THREE.PerspectiveCamera(45, width / height, 0.1, 1000);
    camera.position.z = 5;

    renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setSize(width, height);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    
    THREE.ColorManagement.enabled = true;
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.outputColorSpace = THREE.LinearSRGBColorSpace;
    
    containerRef.value.appendChild(renderer.domElement);

    earthGroup = new THREE.Group();
    earthGroup.rotation.z = (-23.4 * Math.PI) / 180;
    earthGroup.position.set(1.8, 0, 0);
    earthGroup.scale.set(1.3, 1.3, 1.3);
    scene.add(earthGroup);

    controls = new OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;
    controls.dampingFactor = 0.05;
    controls.enableZoom = false;
    controls.enableRotate = true;
    controls.enablePan = false;
    controls.autoRotate = true;
    controls.autoRotateSpeed = 0.5;

    const loader = new THREE.TextureLoader();
    const geometry = new THREE.IcosahedronGeometry(1, 14);
    
    const material = new THREE.MeshPhongMaterial({
        map: loader.load('/images/earth/earthmap.jpg'),
    });
    earthMesh = new THREE.Mesh(geometry, material);
    earthGroup.add(earthMesh);

    const lightsMat = new THREE.MeshBasicMaterial({
        map: loader.load('/images/earth/earth_lights.png'),
        blending: THREE.AdditiveBlending,
    });
    lightsMesh = new THREE.Mesh(geometry, lightsMat);
    earthGroup.add(lightsMesh);

    const cloudsMat = new THREE.MeshStandardMaterial({
        map: loader.load('/images/earth/cloud_combined.jpg'),
        transparent: true,
        opacity: 0.9,
        blending: THREE.AdditiveBlending,
    });
    cloudsMesh = new THREE.Mesh(geometry, cloudsMat);
    cloudsMesh.scale.setScalar(1.003);
    earthGroup.add(cloudsMesh);

    const fresnelMat = getFresnelMat();
    glowMesh = new THREE.Mesh(geometry, fresnelMat);
    glowMesh.scale.setScalar(1.01);
    earthGroup.add(glowMesh);

    stars = getStarfield({ numStars: 5000 });
    scene.add(stars);

    const sunLight = new THREE.DirectionalLight(0xffffff, 2.0);
    sunLight.position.set(-2.2, 0.7, 1.6);
    scene.add(sunLight);

    window.addEventListener('resize', onWindowResize);
    
    // Start particle animation after a short delay
    setTimeout(() => createConnectingParticles(), 200);
};

const getFresnelMat = ({ rimHex = 0x3ABEF9, facingHex = 0x000000 } = {}) => {
    const uniforms = {
        color1: { value: new THREE.Color(rimHex) },
        color2: { value: new THREE.Color(facingHex) },
        fresnelBias: { value: 0.2 },
        fresnelScale: { value: 1.0 },
        fresnelPower: { value: 8.0 },
    };
    
    const vs = `
        uniform float fresnelBias;
        uniform float fresnelScale;
        uniform float fresnelPower;
        
        varying float vReflectionFactor;
        
        void main() {
            vec4 mvPosition = modelViewMatrix * vec4(position, 1.0);
            vec4 worldPosition = modelMatrix * vec4(position, 1.0);
            
            vec3 worldNormal = normalize(mat3(modelMatrix[0].xyz, modelMatrix[1].xyz, modelMatrix[2].xyz) * normal);
            vec3 I = worldPosition.xyz - cameraPosition;
            
            vReflectionFactor = fresnelBias + fresnelScale * pow(1.0 + dot(normalize(I), worldNormal), fresnelPower);
            
            gl_Position = projectionMatrix * mvPosition;
        }
    `;
    
    const fs = `
        uniform vec3 color1;
        uniform vec3 color2;
        
        varying float vReflectionFactor;
        
        void main() {
            float f = clamp(vReflectionFactor, 0.0, 1.0);
            gl_FragColor = vec4(mix(color2, color1, vec3(f)), f);
        }
    `;
    
    return new THREE.ShaderMaterial({
        uniforms: uniforms,
        vertexShader: vs,
        fragmentShader: fs,
        transparent: true,
        blending: THREE.AdditiveBlending,
    });
};

const getStarfield = ({ numStars = 500 } = {}) => {
    const verts = [];
    const colors = [];
    
    for (let i = 0; i < numStars; i++) {
        const radius = Math.random() * 25 + 25;
        const u = Math.random();
        const v = Math.random();
        const theta = 2 * Math.PI * u;
        const phi = Math.acos(2 * v - 1);
        
        const x = radius * Math.sin(phi) * Math.cos(theta);
        const y = radius * Math.sin(phi) * Math.sin(theta);
        const z = radius * Math.cos(phi);
        
        verts.push(x, y, z);
        
        const col = new THREE.Color().setHSL(0.6, 0.4, Math.random());
        colors.push(col.r, col.g, col.b);
    }
    
    const geo = new THREE.BufferGeometry();
    geo.setAttribute('position', new THREE.Float32BufferAttribute(verts, 3));
    geo.setAttribute('color', new THREE.Float32BufferAttribute(colors, 3));
    
    const mat = new THREE.PointsMaterial({
        size: 0.2,
        vertexColors: true,
    });
    
    return new THREE.Points(geo, mat);
};

const createConnectingParticles = () => {
    if (!particlesCanvas.value || !containerRef.value) {
        return;
    }
    
    const canvas = particlesCanvas.value;
    const ctx = canvas.getContext('2d');
    canvas.width = containerRef.value.offsetWidth;
    canvas.height = containerRef.value.offsetHeight;
    
    const particles = [];
    const particleCount = 80;
    
    for (let i = 0; i < particleCount; i++) {
        particles.push({
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height,
            vx: (Math.random() - 0.5) * 1,
            vy: (Math.random() - 0.5) * 1,
            size: Math.random() * 3 + 2,
            opacity: Math.random() * 0.6 + 0.4
        });
    }
    
    const animateParticles = () => {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        particles.forEach((p, i) => {
            p.x += p.vx;
            p.y += p.vy;
            
            if (p.x < 0 || p.x > canvas.width) p.vx *= -1;
            if (p.y < 0 || p.y > canvas.height) p.vy *= -1;
            
            // Draw particle
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(250, 204, 21, ${p.opacity})`;
            ctx.fill();
            
            // Draw connections
            particles.slice(i + 1).forEach(p2 => {
                const dx = p.x - p2.x;
                const dy = p.y - p2.y;
                const distance = Math.sqrt(dx * dx + dy * dy);
                
                if (distance < 200) {
                    ctx.beginPath();
                    ctx.moveTo(p.x, p.y);
                    ctx.lineTo(p2.x, p2.y);
                    ctx.strokeStyle = `rgba(250, 204, 21, ${0.3 * (1 - distance / 200)})`;
                    ctx.lineWidth = 1;
                    ctx.stroke();
                }
            });
        });
        
        particleAnimationId = requestAnimationFrame(animateParticles);
    };
    
    animateParticles();
};

const onWindowResize = () => {
    if (!camera || !renderer || !containerRef.value) return;
    
    const width = containerRef.value.offsetWidth;
    const height = containerRef.value.offsetHeight;
    
    camera.aspect = width / height;
    camera.updateProjectionMatrix();
    renderer.setSize(width, height);
    
    if (particlesCanvas.value) {
        particlesCanvas.value.width = width;
        particlesCanvas.value.height = height;
    }
};

const animate = () => {
    animationId = requestAnimationFrame(animate);

    if (earthMesh) earthMesh.rotation.y += 0.0019;
    if (lightsMesh) lightsMesh.rotation.y += 0.0019;
    if (cloudsMesh) cloudsMesh.rotation.y += 0.0026;
    if (glowMesh) glowMesh.rotation.y += 0.002;
    if (stars) stars.rotation.y -= 0.0002;

    if (controls) controls.update();
    renderer.render(scene, camera);
};

onMounted(() => {
    initGlobe();
    animate();
});

onUnmounted(() => {
    window.removeEventListener('resize', onWindowResize);
    if (animationId) {
        cancelAnimationFrame(animationId);
    }
    if (particleAnimationId) {
        cancelAnimationFrame(particleAnimationId);
    }
    if (renderer) {
        renderer.dispose();
        if (containerRef.value && renderer.domElement) {
            containerRef.value.removeChild(renderer.domElement);
        }
    }
    if (scene) {
        scene.traverse((object) => {
            if (object.geometry) object.geometry.dispose();
            if (object.material) {
                if (Array.isArray(object.material)) {
                    object.material.forEach(material => material.dispose());
                } else {
                    object.material.dispose();
                }
            }
        });
    }
});
</script>

<style scoped>
</style>

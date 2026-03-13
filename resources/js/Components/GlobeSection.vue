<template>
    <section class="relative py-24 overflow-hidden" style="background: linear-gradient(180deg, #0a1628 0%, #162238 100%);">
        <!-- Canvas for Three.js -->
        <canvas ref="globeCanvas" class="absolute inset-0 w-full h-full"></canvas>
        
        <!-- Content Overlay -->
        <div class="relative z-10 container mx-auto px-4 text-center">
            <h1 class="text-5xl md:text-7xl font-bold text-yellow-400 mb-4 animate-fade-in">
                CORPIUS
            </h1>
            <p class="text-xl md:text-2xl text-white font-light tracking-widest">
                GLOBAL BUSINESS SOLUTIONS
            </p>
            
            <!-- Stats -->
            <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-8 max-w-4xl mx-auto">
                <div class="text-center">
                    <div class="text-4xl font-bold text-yellow-400 mb-2">10,000+</div>
                    <div class="text-gray-300">Businesses Formed</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-yellow-400 mb-2">50+</div>
                    <div class="text-gray-300">U.S. States</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-yellow-400 mb-2">100+</div>
                    <div class="text-gray-300">Countries Served</div>
                </div>
            </div>
        </div>
        
        <!-- Particles Background -->
        <div class="absolute inset-0 pointer-events-none">
            <div v-for="i in 50" :key="i" 
                 class="absolute w-1 h-1 bg-yellow-400 rounded-full animate-twinkle"
                 :style="getStarStyle(i)">
            </div>
        </div>
    </section>
</template>

<script setup>
import * as THREE from 'three';
import { onMounted, onUnmounted, ref } from 'vue';

const globeCanvas = ref(null);
let scene, camera, renderer, globe, animationId;
let particleSystem, centralBeam;

const getStarStyle = (index) => {
    const randomTop = Math.random() * 100;
    const randomLeft = Math.random() * 100;
    const randomDelay = Math.random() * 3;
    const randomDuration = 2 + Math.random() * 3;
    
    return {
        top: `${randomTop}%`,
        left: `${randomLeft}%`,
        animationDelay: `${randomDelay}s`,
        animationDuration: `${randomDuration}s`,
        opacity: Math.random() * 0.5 + 0.3
    };
};

const createGlobe = () => {
    // Scene
    scene = new THREE.Scene();
    
    // Camera
    const aspect = window.innerWidth / window.innerHeight;
    camera = new THREE.PerspectiveCamera(45, aspect, 0.1, 1000);
    camera.position.z = 5;
    
    // Renderer
    renderer = new THREE.WebGLRenderer({
        canvas: globeCanvas.value,
        alpha: true,
        antialias: true
    });
    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    
    // Globe wireframe
    const radius = 1.5;
    const segments = 64;
    
    // Create wireframe sphere
    const geometry = new THREE.SphereGeometry(radius, segments, segments);
    const wireframeMaterial = new THREE.MeshBasicMaterial({
        color: 0xfbbf24,
        wireframe: true,
        transparent: true,
        opacity: 0.3
    });
    
    globe = new THREE.Mesh(geometry, wireframeMaterial);
    scene.add(globe);
    
    // Add latitude/longitude lines
    createGridLines(radius, segments);
    
    // Add connection points
    createConnectionPoints(radius, segments);
    
    // Create central beam
    createCentralBeam(radius);
    
    // Lighting
    const ambientLight = new THREE.AmbientLight(0xffffff, 0.5);
    scene.add(ambientLight);
    
    const pointLight = new THREE.PointLight(0xfbbf24, 1, 100);
    pointLight.position.set(0, 0, 5);
    scene.add(pointLight);
};

const createGridLines = (radius, segments) => {
    const gridGroup = new THREE.Group();
    
    // Latitude lines
    for (let i = 0; i <= segments; i += 4) {
        const phi = (Math.PI * i) / segments;
        const circleRadius = radius * Math.sin(phi);
        const y = radius * Math.cos(phi);
        
        const circleGeometry = new THREE.CircleGeometry(circleRadius, 64);
        const edges = new THREE.EdgesGeometry(circleGeometry);
        const line = new THREE.LineSegments(
            edges,
            new THREE.LineBasicMaterial({ color: 0xfbbf24, transparent: true, opacity: 0.4 })
        );
        line.rotation.x = Math.PI / 2;
        line.position.y = y;
        gridGroup.add(line);
    }
    
    // Longitude lines
    for (let i = 0; i < segments; i += 4) {
        const theta = (2 * Math.PI * i) / segments;
        const points = [];
        
        for (let j = 0; j <= segments; j++) {
            const phi = (Math.PI * j) / segments;
            const x = radius * Math.sin(phi) * Math.cos(theta);
            const y = radius * Math.cos(phi);
            const z = radius * Math.sin(phi) * Math.sin(theta);
            points.push(new THREE.Vector3(x, y, z));
        }
        
        const lineGeometry = new THREE.BufferGeometry().setFromPoints(points);
        const lineMaterial = new THREE.LineBasicMaterial({ 
            color: 0xfbbf24, 
            transparent: true, 
            opacity: 0.4 
        });
        const line = new THREE.Line(lineGeometry, lineMaterial);
        gridGroup.add(line);
    }
    
    scene.add(gridGroup);
};

const createConnectionPoints = (radius, segments) => {
    const pointsGeometry = new THREE.BufferGeometry();
    const positions = [];
    
    // Create points at grid intersections
    for (let i = 0; i <= segments; i += 4) {
        for (let j = 0; j < segments; j += 4) {
            const phi = (Math.PI * i) / segments;
            const theta = (2 * Math.PI * j) / segments;
            
            const x = radius * Math.sin(phi) * Math.cos(theta);
            const y = radius * Math.cos(phi);
            const z = radius * Math.sin(phi) * Math.sin(theta);
            
            positions.push(x, y, z);
        }
    }
    
    pointsGeometry.setAttribute('position', new THREE.Float32BufferAttribute(positions, 3));
    
    const pointsMaterial = new THREE.PointsMaterial({
        color: 0xfbbf24,
        size: 0.05,
        transparent: true,
        opacity: 0.8,
        sizeAttenuation: true
    });
    
    const points = new THREE.Points(pointsGeometry, pointsMaterial);
    scene.add(points);
};

const createCentralBeam = (radius) => {
    // Vertical beam through center
    const beamGeometry = new THREE.CylinderGeometry(0.02, 0.02, radius * 3, 32);
    const beamMaterial = new THREE.MeshBasicMaterial({
        color: 0xfbbf24,
        transparent: true,
        opacity: 0.6
    });
    
    centralBeam = new THREE.Mesh(beamGeometry, beamMaterial);
    scene.add(centralBeam);
    
    // Add glowing particles along the beam
    const particlesGeometry = new THREE.BufferGeometry();
    const particlesCount = 100;
    const positions = new Float32Array(particlesCount * 3);
    
    for (let i = 0; i < particlesCount; i++) {
        positions[i * 3] = (Math.random() - 0.5) * 0.1;
        positions[i * 3 + 1] = (Math.random() - 0.5) * radius * 3;
        positions[i * 3 + 2] = (Math.random() - 0.5) * 0.1;
    }
    
    particlesGeometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    
    const particlesMaterial = new THREE.PointsMaterial({
        color: 0xfbbf24,
        size: 0.03,
        transparent: true,
        opacity: 0.8,
        blending: THREE.AdditiveBlending
    });
    
    particleSystem = new THREE.Points(particlesGeometry, particlesMaterial);
    scene.add(particleSystem);
};

const animate = () => {
    animationId = requestAnimationFrame(animate);
    
    // Rotate globe slowly
    if (globe) {
        globe.rotation.y += 0.002;
    }
    
    // Animate central beam particles
    if (particleSystem) {
        const positions = particleSystem.geometry.attributes.position.array;
        
        for (let i = 0; i < positions.length; i += 3) {
            positions[i + 1] += 0.01; // Move particles upward
            
            // Reset particles that go too high
            if (positions[i + 1] > 2.5) {
                positions[i + 1] = -2.5;
            }
        }
        
        particleSystem.geometry.attributes.position.needsUpdate = true;
    }
    
    // Pulse central beam
    if (centralBeam) {
        centralBeam.material.opacity = 0.4 + Math.sin(Date.now() * 0.001) * 0.2;
    }
    
    renderer.render(scene, camera);
};

const handleResize = () => {
    if (!camera || !renderer) return;
    
    camera.aspect = window.innerWidth / window.innerHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(window.innerWidth, window.innerHeight);
};

onMounted(() => {
    createGlobe();
    animate();
    window.addEventListener('resize', handleResize);
});

onUnmounted(() => {
    window.removeEventListener('resize', handleResize);
    if (animationId) {
        cancelAnimationFrame(animationId);
    }
    if (renderer) {
        renderer.dispose();
    }
});
</script>

<style scoped>
@keyframes twinkle {
    0%, 100% { opacity: 0.3; }
    50% { opacity: 1; }
}

@keyframes fade-in {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-twinkle {
    animation: twinkle 3s ease-in-out infinite;
}

.animate-fade-in {
    animation: fade-in 1s ease-out;
}
</style>

import { useEffect, useRef, useState } from "react";

type Particle = {
  x: number;
  y: number;
  size: number;
  speedX: number;
  speedY: number;
  opacity: number;
  symbol: string;
  rotation: number;
  rotationSpeed: number;
};

export function CryptoBackground() {
  const canvasRef = useRef<HTMLCanvasElement>(null);
  const [prefersReduced, setPrefersReduced] = useState(false);

  useEffect(() => {
    const media = typeof window !== "undefined" && window.matchMedia
      ? window.matchMedia("(prefers-reduced-motion: reduce)")
      : null;
    const handleMotionChange = () => setPrefersReduced(Boolean(media?.matches));
    handleMotionChange();
    media?.addEventListener("change", handleMotionChange);
    return () => media?.removeEventListener("change", handleMotionChange);
  }, []);

  useEffect(() => {
    const canvas = canvasRef.current;
    if (!canvas) return;

    const ctx = canvas.getContext("2d");
    if (!ctx) return;

    const resizeCanvas = () => {
      canvas.width = window.innerWidth;
      canvas.height = window.innerHeight;
    };

    resizeCanvas();
    window.addEventListener("resize", resizeCanvas);

    const symbols = ["Φ", "◆", "●", "◇", "○", "₿", "◈"];
    const particles: Particle[] = [];
    const particleCount = prefersReduced ? 16 : 40;

    for (let i = 0; i < particleCount; i++) {
      particles.push({
        x: Math.random() * canvas.width,
        y: Math.random() * canvas.height,
        size: Math.random() * 24 + 14,
        speedX: prefersReduced ? 0 : (Math.random() - 0.5) * 0.8,
        speedY: prefersReduced ? 0 : (Math.random() - 0.5) * 0.8,
        opacity: Math.random() * 0.2 + 0.08,
        symbol: symbols[Math.floor(Math.random() * symbols.length)],
        rotation: Math.random() * Math.PI * 2,
        rotationSpeed: prefersReduced ? 0 : (Math.random() - 0.5) * 0.02,
      });
    }

    const drawConnections = () => {
      for (let i = 0; i < particles.length; i++) {
        for (let j = i + 1; j < particles.length; j++) {
          const dx = particles[i].x - particles[j].x;
          const dy = particles[i].y - particles[j].y;
          const distance = Math.sqrt(dx * dx + dy * dy);

          if (distance < 250) {
            ctx.beginPath();
            ctx.strokeStyle = `rgba(16, 185, 129, ${0.08 * (1 - distance / 250)})`;
            ctx.lineWidth = 1.5;
            ctx.moveTo(particles[i].x, particles[i].y);
            ctx.lineTo(particles[j].x, particles[j].y);
            ctx.stroke();
          }
        }
      }
    };

    let animationId: number | null = null;
    let paused = false;

    const renderFrame = (moveParticles: boolean) => {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      drawConnections();

      particles.forEach((particle) => {
        if (moveParticles) {
          particle.x += particle.speedX;
          particle.y += particle.speedY;
          particle.rotation += particle.rotationSpeed;
          particle.y += Math.sin(Date.now() * 0.001 + particle.x * 0.01) * 0.3;

          if (particle.x < -50) particle.x = canvas.width + 50;
          if (particle.x > canvas.width + 50) particle.x = -50;
          if (particle.y < -50) particle.y = canvas.height + 50;
          if (particle.y > canvas.height + 50) particle.y = -50;
        }

        ctx.save();
        ctx.translate(particle.x, particle.y);
        ctx.rotate(particle.rotation);
        ctx.font = `${particle.size}px Inter, sans-serif`;
        ctx.fillStyle = `rgba(16, 185, 129, ${particle.opacity})`;
        ctx.textAlign = "center";
        ctx.textBaseline = "middle";
        ctx.fillText(particle.symbol, 0, 0);
        ctx.restore();
      });
    };

    const animate = () => {
      renderFrame(true);
      animationId = requestAnimationFrame(animate);
    };

    const start = () => {
      if (prefersReduced || paused) return;
      if (animationId) cancelAnimationFrame(animationId);
      animationId = requestAnimationFrame(animate);
    };

    const stop = () => {
      paused = true;
      if (animationId) cancelAnimationFrame(animationId);
      animationId = null;
    };

    const handleVisibility = () => {
      if (document.hidden) {
        stop();
      } else {
        paused = false;
        start();
      }
    };

    document.addEventListener("visibilitychange", handleVisibility);

    if (prefersReduced) {
      renderFrame(false);
    } else {
      start();
    }

    return () => {
      window.removeEventListener("resize", resizeCanvas);
      document.removeEventListener("visibilitychange", handleVisibility);
      if (animationId) cancelAnimationFrame(animationId);
    };
  }, [prefersReduced]);

  return <canvas ref={canvasRef} className="fixed inset-0 pointer-events-none z-0" />;
}

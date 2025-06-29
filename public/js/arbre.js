document.addEventListener('DOMContentLoaded', function () {
    const svg = document.getElementById('arbre-svg');

    if (!svg) return;

    // Création du groupe racine
    const root = createNode('Nous Autres', 400, 50);
    svg.appendChild(root.circle);
    svg.appendChild(root.text);

    // Exemple de branches
    const branches = [
        { label: 'Cercles Locaux', x: 250, y: 150 },
        { label: 'Projets collectifs', x: 400, y: 150 },
        { label: 'Racines vivantes', x: 550, y: 150 }
    ];

    branches.forEach(branch => {
        const node = createNode(branch.label, branch.x, branch.y);
        svg.appendChild(createLine(400, 50, branch.x, branch.y));
        svg.appendChild(node.circle);
        svg.appendChild(node.text);
    });
});

function createNode(label, x, y) {
    const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
    circle.setAttribute('cx', x);
    circle.setAttribute('cy', y);
    circle.setAttribute('r', 40);
    circle.setAttribute('fill', '#88c0d0');

    const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
    text.setAttribute('x', x);
    text.setAttribute('y', y + 5);
    text.setAttribute('text-anchor', 'middle');
    text.setAttribute('font-size', '14');
    text.setAttribute('fill', '#fff');
    text.textContent = label;

    return { circle, text };
}

function createLine(x1, y1, x2, y2) {
    const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
    line.setAttribute('x1', x1);
    line.setAttribute('y1', y1);
    line.setAttribute('x2', x2);
    line.setAttribute('y2', y2);
    line.setAttribute('stroke', '#ccc');
    line.setAttribute('stroke-width', '2');
    return line;
}



class Branche {
  constructor(x, y, angle, length, depth) {
    this.x = x;
    this.y = y;
    this.angle = angle;
    this.length = length;
    this.depth = depth;
    this.progress = 0;
  }

  draw() {
    if (this.progress < 1) {
      this.progress += 0.01;
    }

    const endX = this.x + Math.cos(this.angle) * this.length * this.progress;
    const endY = this.y + Math.sin(this.angle) * this.length * this.progress;

    ctx.strokeStyle = `hsl(${this.depth * 25}, 40%, 40%)`;
    ctx.lineWidth = Math.max(1, 5 - this.depth);
    ctx.beginPath();
    ctx.moveTo(this.x, this.y);
    ctx.lineTo(endX, endY);
    ctx.stroke();

    if (this.progress >= 1 && this.depth < 5) {
      const angleSpread = Math.PI / 4;
      branches.push(new Branche(endX, endY, this.angle - angleSpread, this.length * 0.7, this.depth + 1));
      branches.push(new Branche(endX, endY, this.angle + angleSpread, this.length * 0.7, this.depth + 1));
    }
  }
}

const canvas = document.getElementById('treeCanvas');
const branches = canvas
  ? [new Branche(canvas.width / 2, canvas.height, -Math.PI / 2, 80, 0)]
  : [];

function animate() {
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  branches.forEach(b => b.draw());

  // Taille branches maximale
  if (branches.length < 100000) {
    requestAnimationFrame(animate);
  }
}

function startTree() {
  if (!canvas) {
    console.warn('Canvas non trouvé.');
    return;
  }
  canvas.width = window.innerWidth;
  canvas.height = window.innerHeight;
  animate();
}



// Charger les données JSON
fetch('/data/projets_arbre.json')
  .then(response => response.json())
  .then(data => {
    if (data.nodes && Array.isArray(data.nodes)) {
      afficherProjets(data.nodes);
    } else {
      console.error("Le format du JSON est incorrect ou vide.");
    }
  })


function afficherProjets(projets) {
  const canvas = document.getElementById('treeCanvas');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');

  projets.forEach((projet, index) => {
    // Positionnement simplifié en cercle
    const angle = (2 * Math.PI / projets.length) * index;
    const x = canvas.width / 2 + 200 * Math.cos(angle);
    const y = canvas.height / 2 + 200 * Math.sin(angle);

    // Affichage du cercle représentant le projet
    ctx.beginPath();
    ctx.arc(x, y, 20, 0, 2 * Math.PI);
    ctx.fillStyle = "#6dd5ed";
    ctx.fill();
    ctx.stroke();

    // Nom du projet
    ctx.fillStyle = "#fff";
    ctx.font = "14px Arial";
    ctx.fillText(projet.label, x - 30, y - 30);
  });
}
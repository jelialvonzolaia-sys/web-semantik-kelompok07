import { Network } from 'vis-network/peer';
import { DataSet } from 'vis-data/peer';

const CLASS_STYLES = {
    RumahAdat: {
        label: 'Rumah Adat',
        shape: 'box',
        color: {
            background: '#B51A28',
            border: '#8E131E',
            highlight: { background: '#991420', border: '#192235' },
            hover: { background: '#991420', border: '#192235' },
        },
        font: { color: '#FFFFFF', size: 12, face: 'sans-serif' },
        size: 24,
        badgeClass: 'bg-[#B51A28] text-white',
        dotClass: 'bg-[#B51A28]',
    },
    Suku: {
        label: 'Suku',
        shape: 'dot',
        color: {
            background: '#192235',
            border: '#192235',
            highlight: { background: '#B51A28', border: '#192235' },
            hover: { background: '#2A3856', border: '#192235' },
        },
        font: { color: '#192235', size: 11, face: 'sans-serif' },
        size: 16,
        badgeClass: 'bg-[#192235] text-white',
        dotClass: 'bg-[#192235]',
    },
    Marga: {
        label: 'Marga',
        shape: 'dot',
        color: {
            background: '#D97706',
            border: '#B45309',
            highlight: { background: '#B51A28', border: '#192235' },
            hover: { background: '#B45309', border: '#192235' },
        },
        font: { color: '#192235', size: 11, face: 'sans-serif' },
        size: 14,
        badgeClass: 'bg-[#FFA586] text-[#192235]',
        dotClass: 'bg-[#D97706]',
    },
    BahasaDaerah: {
        label: 'Bahasa Daerah',
        shape: 'dot',
        color: {
            background: '#1E7E6E',
            border: '#155E52',
            highlight: { background: '#B51A28', border: '#192235' },
            hover: { background: '#155E52', border: '#192235' },
        },
        font: { color: '#192235', size: 11, face: 'sans-serif' },
        size: 14,
        badgeClass: 'bg-[#1E7E6E] text-white',
        dotClass: 'bg-[#1E7E6E]',
    },
    Wilayah: {
        label: 'Wilayah',
        shape: 'dot',
        color: {
            background: '#2563EB',
            border: '#1D4ED8',
            highlight: { background: '#B51A28', border: '#192235' },
            hover: { background: '#1D4ED8', border: '#192235' },
        },
        font: { color: '#192235', size: 11, face: 'sans-serif' },
        size: 15,
        badgeClass: 'bg-[#2563EB] text-white',
        dotClass: 'bg-[#2563EB]',
    },
    Tradisi: {
        label: 'Tradisi',
        shape: 'dot',
        color: {
            background: '#7C3AED',
            border: '#5B21B6',
            highlight: { background: '#B51A28', border: '#192235' },
            hover: { background: '#5B21B6', border: '#192235' },
        },
        font: { color: '#192235', size: 11, face: 'sans-serif' },
        size: 14,
        badgeClass: 'bg-[#7C3AED] text-white',
        dotClass: 'bg-[#7C3AED]',
    },
    Ornamen: {
        label: 'Ornamen',
        shape: 'dot',
        color: {
            background: '#EA580C',
            border: '#C2410C',
            highlight: { background: '#B51A28', border: '#192235' },
            hover: { background: '#C2410C', border: '#192235' },
        },
        font: { color: '#192235', size: 11, face: 'sans-serif' },
        size: 14,
        badgeClass: 'bg-[#EA580C] text-white',
        dotClass: 'bg-[#EA580C]',
    },
};

const FALLBACK_STYLE = {
    label: 'Lainnya',
    shape: 'dot',
    color: { background: '#718096', border: '#4A5568' },
    font: { color: '#192235', size: 11, face: 'sans-serif' },
    size: 14,
    badgeClass: 'bg-slate-600 text-white',
    dotClass: 'bg-slate-600',
};

function getStyle(groupName) {
    return CLASS_STYLES[groupName] || FALLBACK_STYLE;
}

function escapeHtml(text) {
    return String(text ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

document.addEventListener('DOMContentLoaded', () => {
    const pageEl = document.getElementById('kg-page');
    if (!pageEl) return;

    const apiUrl = pageEl.dataset.apiUrl;
    const detailBase = pageEl.dataset.detailBase;
    const searchBase = pageEl.dataset.searchBase;

    const searchInput = document.getElementById('kg-search-input');
    const focusSelect = document.getElementById('kg-focus-select');
    const filterForm = document.getElementById('kg-filter-form');
    const resetBtn = document.getElementById('kg-reset-btn');
    const legendContainer = document.getElementById('kg-legend');
    const showAllClassesBtn = document.getElementById('kg-show-all-classes');
    const canvasEl = document.getElementById('kg-canvas');
    const statusText = document.getElementById('kg-status-text');
    const nodeCountEl = document.getElementById('kg-node-count');
    const edgeCountEl = document.getElementById('kg-edge-count');
    const fusekiAlert = document.getElementById('kg-fuseki-alert');
    const fusekiAlertMsg = document.getElementById('kg-fuseki-alert-msg');

    const zoomInBtn = document.getElementById('kg-zoom-in');
    const zoomOutBtn = document.getElementById('kg-zoom-out');
    const fitBtn = document.getElementById('kg-fit-btn');
    const togglePhysicsBtn = document.getElementById('kg-toggle-physics');

    const inspectorEmpty = document.getElementById('kg-inspector-empty');
    const inspectorContent = document.getElementById('kg-inspector-content');
    const inspectorBadge = document.getElementById('kg-inspector-badge');
    const nodeLabelEl = document.getElementById('kg-node-label');
    const nodeUriEl = document.getElementById('kg-node-uri');
    const nodeActionsEl = document.getElementById('kg-node-actions');
    const neighborCountEl = document.getElementById('kg-neighbor-count');
    const neighborListEl = document.getElementById('kg-neighbor-list');
    const relationsSection = document.getElementById('kg-relations-section');

    let rawNodes = [];
    let rawEdges = [];
    let activeClasses = new Set(Object.keys(CLASS_STYLES));
    let physicsEnabled = true;

    searchInput.value = pageEl.dataset.initialQ || '';
    const initialFocus = pageEl.dataset.initialFocus || '';

    const visNodes = new DataSet([]);
    const visEdges = new DataSet([]);

    const network = new Network(
        canvasEl,
        { nodes: visNodes, edges: visEdges },
        {
            physics: {
                enabled: true,
                solver: 'barnesHut',
                barnesHut: {
                    gravitationalConstant: -4500,
                    centralGravity: 0.25,
                    springLength: 155,
                    springConstant: 0.04,
                    damping: 0.12,
                    avoidOverlap: 0.5,
                },
                stabilization: {
                    enabled: true,
                    iterations: 200,
                    updateInterval: 25,
                    fit: true,
                },
            },
            interaction: {
                hover: true,
                tooltipDelay: 120,
                navigationButtons: false,
                keyboard: false,
            },
            nodes: {
                borderWidth: 1.5,
                shadow: { enabled: true, size: 4, x: 1, y: 2, color: 'rgba(25, 34, 53, 0.15)' },
            },
            edges: {
                smooth: { enabled: true, type: 'dynamic' },
                font: {
                    size: 9,
                    align: 'top',
                    color: '#384358',
                    background: 'rgba(255, 255, 255, 0.85)',
                    strokeWidth: 0,
                },
                color: {
                    color: '#CBD5E1',
                    highlight: '#B51A28',
                    hover: '#192235',
                },
                width: 1.1,
                arrows: { to: { enabled: true, scaleFactor: 0.55 } },
            },
        }
    );

    function populateFocusOptions(nodes, currentFocus) {
        const prevValue = currentFocus || focusSelect.value;
        const houses = nodes
            .filter((n) => n.group === 'RumahAdat' && n.slug)
            .sort((a, b) => String(a.label).localeCompare(String(b.label), 'id'));

        focusSelect.innerHTML = `<option value="">Tampilkan Seluruh Knowledge Graph (${houses.length} Rumah Adat)</option>`;
        for (const h of houses) {
            const opt = document.createElement('option');
            opt.value = h.slug;
            opt.textContent = h.label;
            if (h.slug === prevValue) opt.selected = true;
            focusSelect.appendChild(opt);
        }
    }

    function renderLegend() {
        legendContainer.innerHTML = '';
        let hiddenCount = 0;
        Object.entries(CLASS_STYLES).forEach(([groupKey, style]) => {
            const isActive = activeClasses.has(groupKey);
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.dataset.group = groupKey;
            btn.className = [
                'inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-medium transition',
                isActive
                    ? 'border-[#192235]/15 bg-[#FFF9F5] text-[#192235] hover:border-[#B51A28]'
                    : 'border-gray-200 bg-gray-50 text-[#192235]/40 line-through',
            ].join(' ');
            btn.innerHTML = `<span class="h-2.5 w-2.5 rounded-full ${escapeHtml(style.dotClass)}"></span>${escapeHtml(style.label)}`;
            btn.addEventListener('click', () => {
                if (activeClasses.has(groupKey)) {
                    if (activeClasses.size > 1) activeClasses.delete(groupKey);
                } else {
                    activeClasses.add(groupKey);
                }
                renderLegend();
                applyClassFilter();
            });
            legendContainer.appendChild(btn);
            if (!isActive) hiddenCount++;
        });

        showAllClassesBtn.classList.toggle('hidden', hiddenCount === 0);
    }

    function applyClassFilter() {
        const filteredNodes = rawNodes
            .filter((n) => activeClasses.has(n.group || 'Lainnya'))
            .map((n) => {
                const st = getStyle(n.group);
                return {
                    id: n.id,
                    label: n.label,
                    group: n.group,
                    title: n.title || `${n.label} (${n.type || n.group || 'Entitas'})`,
                    shape: st.shape,
                    color: st.color,
                    font: st.font,
                    size: st.size,
                };
            });

        const visibleNodeIds = new Set(filteredNodes.map((n) => n.id));
        const filteredEdges = rawEdges
            .filter((e) => visibleNodeIds.has(e.from) && visibleNodeIds.has(e.to))
            .map((e, idx) => ({
                id: e.id || `edge-${idx}`,
                from: e.from,
                to: e.to,
                label: e.label || '',
                arrows: e.arrows || 'to',
                title: e.label || e.predicate || '',
            }));

        visNodes.clear();
        visEdges.clear();
        visNodes.add(filteredNodes);
        visEdges.add(filteredEdges);

        nodeCountEl.textContent = String(filteredNodes.length);
        edgeCountEl.textContent = String(filteredEdges.length);
    }

    function renderInspector(nodeId) {
        const rawNode = rawNodes.find((n) => n.id === nodeId);
        if (!rawNode) {
            inspectorEmpty.classList.remove('hidden');
            inspectorContent.classList.add('hidden');
            relationsSection.classList.add('hidden');
            return;
        }

        const st = getStyle(rawNode.group);
        inspectorEmpty.classList.add('hidden');
        inspectorContent.classList.remove('hidden');
        relationsSection.classList.remove('hidden');

        inspectorBadge.className = `inline-block rounded-full px-3.5 py-1 text-xs font-semibold ${st.badgeClass}`;
        inspectorBadge.textContent = st.label || rawNode.type || rawNode.group || 'Entitas';

        nodeLabelEl.textContent = rawNode.label || '(Tanpa Label)';
        nodeUriEl.textContent = rawNode.id || '-';

        const actionButtons = [];
        if (rawNode.group === 'RumahAdat' && rawNode.slug) {
            actionButtons.push(`
                <a href="${detailBase}/${encodeURIComponent(rawNode.slug)}"
                    class="flex w-full items-center justify-center rounded-2xl bg-[#B51A28] px-5 py-3 text-sm font-semibold text-white shadow-xs transition hover:bg-[#991420] focus:outline-none focus:ring-2 focus:ring-[#B51A28]/40">
                    Lihat Detail Rumah Adat
                </a>
            `);
            actionButtons.push(`
                <button type="button" data-focus-slug="${escapeHtml(rawNode.slug)}"
                    class="kg-focus-subgraf flex w-full items-center justify-center rounded-2xl border border-[#192235]/15 bg-white px-5 py-3 text-sm font-semibold text-[#192235] shadow-xs transition hover:border-[#192235]/30 hover:bg-[#FFF9F5] focus:outline-none focus:ring-2 focus:ring-[#192235]/15">
                    Fokuskan Subgraf Entitas Ini
                </button>
            `);
        } else if (rawNode.label) {
            actionButtons.push(`
                <a href="${searchBase}?q=${encodeURIComponent(rawNode.label)}"
                    class="flex w-full items-center justify-center rounded-2xl bg-[#B51A28] px-5 py-3 text-sm font-semibold text-white shadow-xs transition hover:bg-[#991420] focus:outline-none focus:ring-2 focus:ring-[#B51A28]/40">
                    Cari Rumah Adat Terkait
                </a>
            `);
        }
        nodeActionsEl.className = 'flex flex-col gap-2.5 pt-2';
        nodeActionsEl.innerHTML = actionButtons.join('');

        const focusSubgrafBtn = nodeActionsEl.querySelector('.kg-focus-subgraf');
        if (focusSubgrafBtn) {
            focusSubgrafBtn.addEventListener('click', () => {
                const slug = focusSubgrafBtn.dataset.focusSlug;
                focusSelect.value = slug;
                loadGraph({ focus: slug, q: searchInput.value.trim() });
            });
        }

        const neighbors = [];
        for (const edge of rawEdges) {
            if (edge.from === nodeId) {
                const target = rawNodes.find((n) => n.id === edge.to);
                if (target) {
                    neighbors.push({
                        node: target,
                        relation: edge.label || 'terhubung ke',
                        direction: '→',
                    });
                }
            } else if (edge.to === nodeId) {
                const source = rawNodes.find((n) => n.id === edge.from);
                if (source) {
                    neighbors.push({
                        node: source,
                        relation: edge.label || 'terhubung dari',
                        direction: '←',
                    });
                }
            }
        }

        neighborCountEl.textContent = String(neighbors.length);
        if (neighbors.length === 0) {
            neighborListEl.innerHTML = '<li class="rounded-xl border border-dashed border-[#192235]/15 bg-[#FFF9F5] p-3 text-center text-[#192235]/55">Tidak ada node tetangga pada graf aktif.</li>';
        } else {
            neighborListEl.innerHTML = neighbors
                .map((item) => {
                    const nStyle = getStyle(item.node.group);
                    return `
                        <li>
                            <button type="button" data-neighbor-id="${escapeHtml(item.node.id)}"
                                class="kg-neighbor-item w-full rounded-xl border border-[#192235]/10 bg-[#FFF9F5] px-3.5 py-2.5 text-left transition hover:border-[#B51A28]">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-mono text-[11px] text-[#B51A28]">-[${escapeHtml(item.relation)}]${escapeHtml(item.direction)}</span>
                                    <span class="rounded-md bg-white px-2 py-0.5 text-[10px] font-medium text-[#192235]/70 ring-1 ring-[#192235]/10">
                                        ${escapeHtml(nStyle.label)}
                                    </span>
                                </div>
                                <div class="mt-1 text-xs font-semibold text-[#192235]">
                                    ${escapeHtml(item.node.label)}
                                </div>
                            </button>
                        </li>
                    `;
                })
                .join('');

            neighborListEl.querySelectorAll('.kg-neighbor-item').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const targetId = btn.dataset.neighborId;
                    network.selectNodes([targetId]);
                    network.focus(targetId, { scale: 1.15, animation: { duration: 400 } });
                    renderInspector(targetId);
                });
            });
        }
    }

    async function loadGraph({ focus = '', q = '' } = {}) {
        statusText.textContent = 'Memuat data graf...';
        try {
            const url = new URL(apiUrl, window.location.origin);
            if (focus) url.searchParams.set('focus', focus);
            if (q) url.searchParams.set('q', q);

            const res = await fetch(url.toString(), { headers: { Accept: 'application/json' } });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);

            const payload = await res.json();
            const meta = payload.meta || {};

            if (meta.fusekiAvailable === false) {
                fusekiAlert.classList.remove('hidden');
                fusekiAlertMsg.textContent = meta.fusekiError || 'Pastikan layanan Fuseki berjalan di localhost:3030 dengan dataset /rumah_adat.';
            } else {
                fusekiAlert.classList.add('hidden');
            }

            rawNodes = Array.isArray(payload.nodes) ? payload.nodes : [];
            rawEdges = Array.isArray(payload.edges) ? payload.edges : [];

            populateFocusOptions(rawNodes, focus);
            applyClassFilter();
            renderInspector(null);

            statusText.textContent = `Siap • ${rawNodes.length} node, ${rawEdges.length} relasi dari backend`;
        } catch (err) {
            statusText.textContent = 'Gagal memuat data graf';
            fusekiAlert.classList.remove('hidden');
            fusekiAlertMsg.textContent = `Terjadi kesalahan saat menghubungi endpoint graf (${err.message}).`;
        }
    }

    network.on('click', (params) => {
        if (params.nodes && params.nodes.length > 0) {
            renderInspector(params.nodes[0]);
        } else {
            renderInspector(null);
        }
    });

    filterForm.addEventListener('submit', (e) => {
        e.preventDefault();
        loadGraph({ focus: focusSelect.value, q: searchInput.value.trim() });
    });

    resetBtn.addEventListener('click', () => {
        searchInput.value = '';
        focusSelect.value = '';
        activeClasses = new Set(Object.keys(CLASS_STYLES));
        renderLegend();
        loadGraph({ focus: '', q: '' });
    });

    showAllClassesBtn.addEventListener('click', () => {
        activeClasses = new Set(Object.keys(CLASS_STYLES));
        renderLegend();
        applyClassFilter();
    });

    zoomInBtn.addEventListener('click', () => network.moveTo({ scale: network.getScale() * 1.25, animation: { duration: 250 } }));
    zoomOutBtn.addEventListener('click', () => network.moveTo({ scale: network.getScale() * 0.8, animation: { duration: 250 } }));
    fitBtn.addEventListener('click', () => network.fit({ animation: { duration: 400 } }));
    togglePhysicsBtn.addEventListener('click', () => {
        physicsEnabled = !physicsEnabled;
        network.setOptions({ physics: { enabled: physicsEnabled } });
        togglePhysicsBtn.textContent = physicsEnabled ? 'Jeda Fisika' : 'Aktifkan Fisika';
    });

    renderLegend();
    loadGraph({ focus: initialFocus, q: searchInput.value.trim() });
});
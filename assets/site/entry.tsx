import React from 'react';
import ReactDOM from 'react-dom/client';
import LiveSearch from './components/LiveSearch';
import EstateSlideshow from './components/EstateSlideshow';
import FavoriteButton from './components/FavoriteButton';
import CommentForm from './components/CommentForm';
import EstateListing from './components/EstateListing';
import SearchResult from './components/SearchResult';
import EstateInfoPanel from './components/EstateInfoPanel';

function mountIsland<P extends object>(
    id: string,
    Component: React.ComponentType<P>,
    props: P,
): void {
    const el = document.getElementById(id);
    if (el) {
        ReactDOM.createRoot(el).render(
            <React.StrictMode>
                <Component {...props} />
            </React.StrictMode>,
        );
    }
}

// LiveSearch
const lsEl = document.getElementById('react-live-search');
if (lsEl) {
    mountIsland('react-live-search', LiveSearch, {
        placeholder: lsEl.dataset.placeholder ?? '',
        locale: lsEl.dataset.locale ?? 'uk',
    });
}

// EstateSlideshow
const slideshowEl = document.getElementById('react-estate-slideshow');
if (slideshowEl) {
    const images: string[] = JSON.parse(slideshowEl.dataset.images ?? '[]');
    mountIsland('react-estate-slideshow', EstateSlideshow, { images });
}

// FavoriteButton
const fbEl = document.getElementById('react-favorite-button');
if (fbEl) {
    mountIsland('react-favorite-button', FavoriteButton, {
        slug: fbEl.dataset.slug ?? '',
        favorited: fbEl.dataset.favorited === 'true',
        csrf: fbEl.dataset.csrf ?? '',
        labelAdd: fbEl.dataset.labelAdd ?? '',
        labelRemove: fbEl.dataset.labelRemove ?? '',
    });
}

// CommentForm
const cfEl = document.getElementById('react-comment-form');
if (cfEl) {
    mountIsland('react-comment-form', CommentForm, {
        slug: cfEl.dataset.slug ?? '',
        csrf: cfEl.dataset.csrf ?? '',
        placeholder: cfEl.dataset.placeholder ?? '',
        submit: cfEl.dataset.submit ?? 'Submit',
    });
}

// EstateListing
const estateListingEl = document.getElementById('react-estate-listing');
if (estateListingEl) {
    const apiUrl = estateListingEl.dataset.apiUrl ?? '/api/public/estates';
    mountIsland('react-estate-listing', EstateListing, { apiUrl });
}

// SearchResult
const searchResultsEl = document.getElementById('react-search-results');
if (searchResultsEl) {
    const categorySlug = searchResultsEl.dataset.category ?? '';
    const districtSlug = searchResultsEl.dataset.district ?? '';
    const price        = searchResultsEl.dataset.price ?? '';
    const exceptFloor  = searchResultsEl.dataset.exceptFloor ?? '0';
    mountIsland('react-search-results', SearchResult, { categorySlug, districtSlug, price, exceptFloor });
}

// EstateInfoPanel
const estateInfoPanelEl = document.getElementById('react-estate-info-panel');
if (estateInfoPanelEl) {
    const slug = estateInfoPanelEl.dataset.slug ?? '';
    mountIsland('react-estate-info-panel', EstateInfoPanel, { slug });
}
